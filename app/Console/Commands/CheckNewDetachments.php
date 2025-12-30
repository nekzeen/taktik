<?php

namespace App\Console\Commands;

use App\Mail\SecurityAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class CheckNewDetachments extends Command
{
    protected $signature = 'detachments:check-new {--force-email : Envoyer l\'email même si déjà notifié}';

    protected $description = 'Vérifie quotidiennement si de nouveaux détachements Wahapedia sont disponibles et envoie un email si nécessaire (sans importer).';

    public function handle(): int
    {
        $to = config('security.alert_email');
        if (!$to) {
            $this->warn('SECURITY_ALERT_EMAIL non configuré.');
            return self::SUCCESS;
        }

        try {
            $missing = $this->fetchMissingDetachments();

            if (count($missing) === 0) {
                $this->info('Aucun nouveau détachement détecté.');
                return self::SUCCESS;
            }

            $hash = sha1(json_encode(array_map(static fn ($m) => $m['id'], $missing)));
            $cacheKey = 'detachments:new:last_hash';

            $alreadyNotified = Cache::get($cacheKey) === $hash;
            if ($alreadyNotified && !$this->option('force-email')) {
                $this->info('Nouveaux détachements déjà notifiés (hash identique).');
                return self::SUCCESS;
            }

            Mail::to($to)->send(new SecurityAlert(
                mailSubject: 'Nouveaux détachements Wahapedia détectés',
                type: 'detachments.new_detected',
                payload: [
                    'count' => count($missing),
                    'items' => $missing,
                ],
            ));

            Cache::put($cacheKey, $hash, now()->addDays(14));

            $this->info('Email envoyé : nouveaux détachements détectés.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Erreur: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * @return array<int, array{id:string,faction_id:string,name:string}>
     */
    private function fetchMissingDetachments(): array
    {
        $url = 'http://wahapedia.ru/wh40k10ed/Detachments.csv';
        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException('Téléchargement Wahapedia échoué (HTTP ' . $response->status() . ')');
        }

        $csv = (string) $response->body();
        $lines = preg_split("/\r\n|\n|\r/", trim($csv));

        if (!$lines || count($lines) < 2) {
            throw new \RuntimeException('Fichier CSV vide ou invalide');
        }

        $headerLine = array_shift($lines);
        $rawHeader = str_getcsv((string) $headerLine, '|');
        $header = array_map(static fn ($h) => ltrim(trim((string) $h), "\xEF\xBB\xBF"), $rawHeader);
        $idx = array_flip($header);

        foreach (['id', 'name', 'faction_id'] as $col) {
            if (!isset($idx[$col])) {
                throw new \RuntimeException('Format CSV inattendu (colonne manquante: ' . $col . ')');
            }
        }

        $existing = [];
        foreach (DB::table('detachments')->pluck('wahapedia_id') as $value) {
            $s = trim((string) $value);
            if ($s === '') {
                continue;
            }
            $existing[ltrim($s, '0') ?: '0'] = true;
        }

        $missing = [];
        foreach ($lines as $line) {
            if (trim((string) $line) === '') {
                continue;
            }

            $row = str_getcsv((string) $line, '|');
            $id = trim((string) ($row[$idx['id']] ?? ''));

            if ($id === '') {
                continue;
            }

            $idNorm = ltrim($id, '0') ?: '0';

            if (!isset($existing[$idNorm])) {
                $missing[] = [
                    'id' => $id,
                    'faction_id' => trim((string) ($row[$idx['faction_id']] ?? '')),
                    'name' => trim((string) ($row[$idx['name']] ?? '')),
                ];
            }
        }

        return $missing;
    }
}
