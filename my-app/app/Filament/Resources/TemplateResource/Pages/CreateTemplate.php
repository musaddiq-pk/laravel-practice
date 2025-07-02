<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Template;
use App\Jobs\TemplateJob;

class CreateTemplate extends CreateRecord
{
    protected static string $resource = TemplateResource::class;

    protected function handleRecordCreation(array $data): Template
    {
        // Proceed with creation
        $template = Template::create($data);

        // Dispatch another job after creation
        TemplateJob::dispatch("Created Template ID: {$template->id}", 1);

        return $template;
    }
}
