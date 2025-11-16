<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['receptionist_id'] = Auth::id();
        $data['appointment_number'] = Appointment::generateAppointmentNumber();
        $data['queue_position'] = Appointment::getNextQueuePosition();
        $data['checked_in_at'] = now();

        return $data;
    }
}
