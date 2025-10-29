<?php

namespace App\Observers;

use App\Models\BsdataDetachment;

class BsdataDetachmentObserver
{
    /**
     * Handle the BsdataDetachment "created" event.
     */
    public function created(BsdataDetachment $bsdataDetachment): void
    {
        // Les créations manuelles sont marquées comme modifiées
        if (!$bsdataDetachment->isDirty('manually_modified')) {
            $bsdataDetachment->update(['manually_modified' => true]);
        }
    }

    /**
     * Handle the BsdataDetachment "updated" event.
     */
    public function updated(BsdataDetachment $bsdataDetachment): void
    {
        // Marquer comme modifié manuellement si des champs importants ont changé
        $importantFields = ['name', 'description', 'is_manual'];
        
        foreach ($importantFields as $field) {
            if ($bsdataDetachment->isDirty($field)) {
                $bsdataDetachment->update(['manually_modified' => true]);
                break;
            }
        }
    }

    /**
     * Handle the BsdataDetachment "deleted" event.
     */
    public function deleted(BsdataDetachment $bsdataDetachment): void
    {
        // Les suppressions sont aussi marquées comme modifiées manuellement
        // (pour éviter la réimportation)
        $bsdataDetachment->update(['manually_modified' => true]);
    }

    /**
     * Handle the BsdataDetachment "restored" event.
     */
    public function restored(BsdataDetachment $bsdataDetachment): void
    {
        //
    }

    /**
     * Handle the BsdataDetachment "force deleted" event.
     */
    public function forceDeleted(BsdataDetachment $bsdataDetachment): void
    {
        //
    }
}
