<?php

namespace App\Filament\Resources\RuleDiscussionCategoryResource\Pages;

use App\Filament\Resources\RuleDiscussionCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRuleDiscussionCategory extends EditRecord
{
    protected static string $resource = RuleDiscussionCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
