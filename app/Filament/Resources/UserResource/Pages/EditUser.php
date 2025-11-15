<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si le mot de passe est vide lors de l'édition, ne pas le mettre à jour
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            // Sinon, le hasher
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }
}
