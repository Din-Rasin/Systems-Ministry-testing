<?php

namespace App\Filament\Resources\MissionRequestResource\Pages;

use App\Filament\Resources\MissionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMissionRequests extends ListRecords
{
    protected static string $resource = MissionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
