<?php

namespace App\Filament\Resources\MissionRequestResource\Pages;

use App\Filament\Resources\MissionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMissionRequest extends EditRecord
{
    protected static string $resource = MissionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
