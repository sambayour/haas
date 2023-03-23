<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'provider',
        'webhook_response',
        'reference',
        'order_ref',
        'amount',
        'payment_link',
        'status',
        'paid',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'payment_link',
        'paid',
    ];

    protected $casts = [
        'webhook_response' => 'array',
        'paid' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->select(['id', 'first_name', 'last_name', 'email']);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'order_ref', 'order_ref')->select(['id', 'order_ref', 'duration_minutes', 'appointment_date', 'notes']);
    }
}
