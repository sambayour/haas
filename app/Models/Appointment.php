<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'duration_minutes',
        'order_ref',
        'notes',
        'appointment_date',
        'status',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'paid' => 'boolean',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'order_ref')->select(['id', 'provider', 'amount', 'paid']);
    }
}
