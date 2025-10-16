<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class WorkflowDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationGroup = 'Workflow Management';

    protected static string $view = 'filament.pages.workflow-dashboard';
    
    protected static ?string $title = 'Workflow System Overview';
    
    protected static ?string $navigationLabel = 'Workflow Dashboard';
}