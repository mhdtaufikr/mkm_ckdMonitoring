<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteJourney extends Model
{
    use HasFactory;

    protected $table = 'delivery_note_journeys'; // Table name

    protected $fillable = [
        'delivery_note_id',
        'status',
        'scanned_at',
        'location',
        'remarks',
    ];

    // Define relationship with the DeliveryNote model
    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class, 'delivery_note_id');
    }
}
