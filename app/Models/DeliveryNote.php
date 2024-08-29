<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'delivery_notes';

    // Define the primary key if it's different from 'id'
    protected $primaryKey = 'id';

    // Disable timestamps if not using created_at and updated_at
    public $timestamps = false;

    // Define the fillable fields for mass assignment
    protected $fillable = [
        'delivery_note_number',
        'customer_po_number',
        'order_number',
        'customer_number',
        'driver_license',
        'destination',
        'date',
        'plat_no',
        'transportation'
    ];

    /**
     * Get the details associated with the delivery note.
     */
    public function details()
    {
        return $this->hasMany(DeliveryNoteDetail::class, 'dn_id', 'id');
    }
}
