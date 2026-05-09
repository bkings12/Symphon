<?php

namespace App\Filament\Resources\Medicines\Pages;

use App\Filament\Resources\Medicines\MedicineResource;
use App\Models\Medicine;
use App\Support\MedicineDeletion;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewMedicine extends ViewRecord
{
    protected static string $resource = MedicineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make()
                ->using(fn (Medicine $record): bool => MedicineDeletion::attemptDelete($record))
                ->failureNotification(fn (): Notification => Notification::make()),
        ];
    }
}
