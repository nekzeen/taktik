<?php

namespace App\Filament\Resources\RuleDiscussionResource\Pages;

use App\Filament\Resources\RuleDiscussionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRuleDiscussions extends ListRecords
{
    protected static string $resource = RuleDiscussionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
