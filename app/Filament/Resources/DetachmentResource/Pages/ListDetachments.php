<?php

namespace App\Filament\Resources\DetachmentResource\Pages;

use App\Filament\Resources\DetachmentResource;
use App\Models\Detachment;
use App\Models\Translation;
use App\Services\TranslationService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class ListDetachments extends ListRecords
{
    protected static string $resource = DetachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verification')
                ->label('Vérification')
                ->icon('heroicon-o-magnifying-glass')
                ->modalHeading('Vérification Wahapedia')
                ->modalSubmitActionLabel('Importer')
                ->modalCancelActionLabel('Fermer')
                ->action(fn () => $this->importMissingDetachments())
                ->modalContent(fn () => $this->buildWahapediaVerificationHtml()),
            Actions\CreateAction::make(),
        ];
    }

    protected function buildWahapediaVerificationHtml(): HtmlString
    {
        try {
            $url = 'http://wahapedia.ru/wh40k10ed/Detachments.csv';
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                return new HtmlString(
                    '<div class="space-y-2">'
                    . '<div class="font-semibold text-danger-600">Téléchargement impossible</div>'
                    . '<div class="text-sm text-gray-600">HTTP ' . e((string) $response->status()) . '</div>'
                    . '</div>'
                );
            }

            $csv = (string) $response->body();
            $lines = preg_split("/\r\n|\n|\r/", trim($csv));

            if (!$lines || count($lines) < 2) {
                return new HtmlString(
                    '<div class="space-y-2">'
                    . '<div class="font-semibold text-danger-600">Fichier CSV vide ou invalide</div>'
                    . '</div>'
                );
            }

            $headerLine = array_shift($lines);
            $rawHeader = str_getcsv((string) $headerLine, '|');
            $header = array_map(static fn ($h) => ltrim(trim((string) $h), "\xEF\xBB\xBF"), $rawHeader);
            $idx = array_flip($header);

            foreach (['id', 'name', 'faction_id'] as $col) {
                if (!isset($idx[$col])) {
                    return new HtmlString(
                        '<div class="space-y-2">'
                        . '<div class="font-semibold text-danger-600">Format CSV inattendu</div>'
                        . '<div class="text-sm text-gray-600">Colonne manquante: ' . e($col) . '</div>'
                        . '</div>'
                    );
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

            $wahapediaCount = count($lines);
            $dbCount = DB::table('detachments')->count();
            $missingCount = count($missing);

            $html = '<div class="space-y-4 text-gray-900 dark:text-gray-100">'
                . '<div class="grid grid-cols-1 gap-2 text-sm">'
                . '<div><span class="font-semibold">Wahapedia:</span> ' . e((string) $wahapediaCount) . ' lignes</div>'
                . '<div><span class="font-semibold">Base:</span> ' . e((string) $dbCount) . ' détachements</div>'
                . '<div><span class="font-semibold">Manquants en base:</span> ' . e((string) $missingCount) . '</div>'
                . '</div>';

            if ($missingCount === 0) {
                $html .= '<div class="rounded-lg bg-success-50 p-3 text-success-700 dark:bg-success-950/30 dark:text-success-200">Aucun nouveau détachement détecté.</div>';
                $html .= '</div>';
                return new HtmlString($html);
            }

            $html .= '<div class="overflow-auto rounded-lg border border-gray-200 dark:border-gray-700">'
                . '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">'
                . '<thead class="bg-gray-50 dark:bg-gray-800">'
                . '<tr>'
                . '<th class="px-3 py-2 text-left font-semibold">Wahapedia ID</th>'
                . '<th class="px-3 py-2 text-left font-semibold">Faction</th>'
                . '<th class="px-3 py-2 text-left font-semibold">Nom</th>'
                . '</tr>'
                . '</thead>'
                . '<tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">';

            foreach ($missing as $m) {
                $html .= '<tr>'
                    . '<td class="px-3 py-2 font-mono">' . e($m['id']) . '</td>'
                    . '<td class="px-3 py-2">' . e($m['faction_id']) . '</td>'
                    . '<td class="px-3 py-2">' . e($m['name']) . '</td>'
                    . '</tr>';
            }

            $html .= '</tbody></table></div></div>';

            return new HtmlString($html);
        } catch (\Throwable $e) {
            return new HtmlString(
                '<div class="space-y-2">'
                . '<div class="font-semibold text-danger-600">Erreur pendant la vérification</div>'
                . '<div class="text-sm text-gray-600">' . e($e->getMessage()) . '</div>'
                . '</div>'
            );
        }
    }

    protected function importMissingDetachments(): void
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        $translationService = new TranslationService();

        try {
            $url = 'http://wahapedia.ru/wh40k10ed/Detachments.csv';
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                Notification::make()
                    ->title('Import impossible')
                    ->body('Téléchargement Wahapedia échoué (HTTP ' . $response->status() . ')')
                    ->danger()
                    ->send();
                return;
            }

            $csv = (string) $response->body();
            $lines = preg_split("/\r\n|\n|\r/", trim($csv));

            if (!$lines || count($lines) < 2) {
                Notification::make()
                    ->title('Import impossible')
                    ->body('Fichier CSV vide ou invalide')
                    ->danger()
                    ->send();
                return;
            }

            $headerLine = array_shift($lines);
            $rawHeader = str_getcsv((string) $headerLine, '|');
            $header = array_map(static fn ($h) => ltrim(trim((string) $h), "\xEF\xBB\xBF"), $rawHeader);
            $idx = array_flip($header);

            foreach (['id', 'name', 'faction_id'] as $col) {
                if (!isset($idx[$col])) {
                    Notification::make()
                        ->title('Import impossible')
                        ->body('Format CSV inattendu (colonne manquante: ' . $col . ')')
                        ->danger()
                        ->send();
                    return;
                }
            }

            $factionMap = DB::table('factions')
                ->pluck('id', 'bsdata_id')
                ->mapWithKeys(fn ($id, $code) => [(string) $code => (int) $id])
                ->all();

            DB::transaction(function () use ($lines, $idx, $factionMap, $translationService, &$imported, &$skipped, &$errors) {
                foreach ($lines as $line) {
                    if (trim((string) $line) === '') {
                        continue;
                    }

                    $row = str_getcsv((string) $line, '|');
                    $wahapediaId = trim((string) ($row[$idx['id']] ?? ''));
                    $name = trim((string) ($row[$idx['name']] ?? ''));
                    $factionCode = trim((string) ($row[$idx['faction_id']] ?? ''));

                    if ($wahapediaId === '' || $name === '' || $factionCode === '') {
                        $skipped++;
                        continue;
                    }

                    if (!isset($factionMap[$factionCode])) {
                        $errors[] = "Faction inconnue: {$factionCode} (detachments.csv id={$wahapediaId}, name={$name})";
                        $skipped++;
                        continue;
                    }

                    $exists = DB::table('detachments')->where('wahapedia_id', $wahapediaId)->exists();
                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    try {
                        $detachment = Detachment::create([
                            'wahapedia_id' => $wahapediaId,
                            'faction_id' => $factionMap[$factionCode],
                            'name' => $name,
                            'description' => null,
                        ]);

                        $this->triggerAutoTranslations($detachment, $translationService);

                        $imported++;
                    } catch (\Throwable $e) {
                        $errors[] = "Import detachment échoué (id={$wahapediaId}, name={$name}): {$e->getMessage()}";
                    }
                }
            });

            $body = "Import terminé. Importés: {$imported}. Ignorés: {$skipped}.";
            if (!empty($errors)) {
                $body .= ' Erreurs: ' . count($errors) . '.';
            }

            Notification::make()
                ->title('Import des détachements')
                ->body($body)
                ->success()
                ->send();

            if (!empty($errors)) {
                Notification::make()
                    ->title('Détails des erreurs')
                    ->body(implode("\n", array_slice($errors, 0, 5)) . (count($errors) > 5 ? "\n..." : ''))
                    ->warning()
                    ->send();
            }

            $this->redirect(static::$resource::getUrl('index'));
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Erreur pendant l\'import')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function triggerAutoTranslations(Detachment $detachment, TranslationService $translationService): void
    {
        $fields = ['name', 'description'];
        $translator = (string) config('translation.default', 'deepl');

        foreach ($fields as $field) {
            try {
                $sourceText = trim((string) $detachment->{$field});

                if ($sourceText === '') {
                    continue;
                }

                $existing = Translation::where('resource_type', 'Detachment')
                    ->where('resource_id', $detachment->id)
                    ->where('field', $field)
                    ->where('locale', 'fr')
                    ->first();

                if ($existing) {
                    continue;
                }

                $translatedText = $translationService->translate(
                    $sourceText,
                    'en',
                    'fr',
                    $translator,
                );

                $status = $translatedText === $sourceText ? 'pending' : 'auto';

                Translation::create([
                    'source_text' => $sourceText,
                    'translated_text' => $translatedText,
                    'locale' => 'fr',
                    'resource_type' => 'Detachment',
                    'resource_id' => $detachment->id,
                    'field' => $field,
                    'status' => $status,
                ]);
            } catch (\Throwable $e) {
                \Log::error("Traduction échouée pour Detachment {$detachment->id} ({$field}): {$e->getMessage()}");
            }
        }
    }
}
