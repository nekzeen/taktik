<?php

namespace App\Filament\Resources\RuleDiscussionReplyResource\Pages;

use App\Filament\Resources\RuleDiscussionReplyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRuleDiscussionReply extends EditRecord
{
    protected static string $resource = RuleDiscussionReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
