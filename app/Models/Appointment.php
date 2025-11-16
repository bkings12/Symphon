<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'customer_id',
        'doctor_id',
        'receptionist_id',
        'appointment_number',
        'reason',
        'status',
        'queue_position',
        'checked_in_at',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function receptionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receptionist_id');
    }

    public static function generateAppointmentNumber(): string
    {
        $lastAppointment = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastAppointment ? $lastAppointment->id + 1 : 1;
        return 'APT-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public static function getNextQueuePosition(): int
    {
        $lastPosition = self::where('status', 'waiting')
            ->orWhere('status', 'in_progress')
            ->max('queue_position');
        
        return ($lastPosition ?? 0) + 1;
    }
}
