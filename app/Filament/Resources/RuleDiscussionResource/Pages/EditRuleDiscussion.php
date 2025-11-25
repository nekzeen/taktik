<?php

namespace App\Filament\Resources\RuleDiscussionResource\Pages;

use App\Filament\Resources\RuleDiscussionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRuleDiscussion extends EditRecord
{
    protected static string $resource = RuleDiscussionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
