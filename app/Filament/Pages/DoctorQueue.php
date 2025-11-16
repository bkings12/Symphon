<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DoctorQueue extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $navigationLabel = 'Patient Queue';

    protected static string|UnitEnum|null $navigationGroup = 'Reception';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.doctor-queue';

    public $appointments = [];
    public $selectedAppointment = null;

    protected $pollingInterval = '5s';

    public function mount()
    {
        $this->loadAppointments();
    }

    public function loadAppointments()
    {
        $this->appointments = Appointment::with(['customer', 'doctor', 'receptionist'])
            ->whereIn('status', ['waiting', 'in_progress'])
            ->orderBy('queue_position', 'asc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'appointment_number' => $appointment->appointment_number,
                    'patient_name' => $appointment->customer->name,
                    'patient_phone' => $appointment->customer->phone,
                    'reason' => $appointment->reason,
                    'queue_position' => $appointment->queue_position,
                    'status' => $appointment->status,
                    'checked_in_at' => $appointment->checked_in_at?->format('H:i'),
                    'doctor_name' => $appointment->doctor?->name ?? 'Not Assigned',
                ];
            })
            ->toArray();
    }

    public function startAppointment($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);
        
        if (!$appointment) {
            $this->dispatch('notify', message: 'Appointment not found', type: 'error');
            return;
        }

        if ($appointment->status === 'in_progress') {
            $this->dispatch('notify', message: 'This appointment is already in progress', type: 'warning');
            return;
        }

        $appointment->update([
            'status' => 'in_progress',
            'doctor_id' => Auth::id(),
            'started_at' => now(),
        ]);

        $this->loadAppointments();
        $this->dispatch('notify', message: 'Appointment started', type: 'success');
    }

    public function completeAppointment($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);
        
        if (!$appointment) {
            $this->dispatch('notify', message: 'Appointment not found', type: 'error');
            return;
        }

        $appointment->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->loadAppointments();
        $this->selectedAppointment = null;
        $this->dispatch('notify', message: 'Appointment completed', type: 'success');
    }

    public function viewAppointment($appointmentId)
    {
        $appointment = Appointment::with(['customer', 'doctor', 'receptionist'])->find($appointmentId);
        
        if ($appointment) {
            $this->selectedAppointment = [
                'id' => $appointment->id,
                'appointment_number' => $appointment->appointment_number,
                'patient_name' => $appointment->customer->name,
                'patient_phone' => $appointment->customer->phone,
                'patient_email' => $appointment->customer->email,
                'patient_dob' => $appointment->customer->date_of_birth?->format('d/m/Y'),
                'patient_gender' => $appointment->customer->gender,
                'patient_address' => $appointment->customer->address,
                'patient_medical_history' => $appointment->customer->medical_history,
                'patient_allergies' => $appointment->customer->allergies,
                'reason' => $appointment->reason,
                'notes' => $appointment->notes,
                'queue_position' => $appointment->queue_position,
                'status' => $appointment->status,
                'checked_in_at' => $appointment->checked_in_at?->format('d/m/Y H:i'),
                'started_at' => $appointment->started_at?->format('d/m/Y H:i'),
                'doctor_name' => $appointment->doctor?->name ?? 'Not Assigned',
                'receptionist_name' => $appointment->receptionist?->name ?? 'N/A',
            ];
        }
    }

    public function closeAppointmentView()
    {
        $this->selectedAppointment = null;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
