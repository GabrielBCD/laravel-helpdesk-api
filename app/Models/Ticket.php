<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_OPEN      = 'open';
    const STATUS_VERIFYING = 'verifying';
    const STATUS_FINISHED  = 'finished';

    protected $fillable = [
        'syndic_name',
        'phone',
        'condominium_name',
        'zip_code',
        'email',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    /**
     * Get the notes for the ticket.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(TicketNote::class);
    }
}

