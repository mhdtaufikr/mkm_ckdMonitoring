<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteDetail extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'delivery_note_details';

    // Define the primary key if it's different from 'id'
    protected $primaryKey = 'id';

    // Disable timestamps if not using created_at and updated_at
    public $timestamps = false;

    // Define the fillable fields for mass assignment
    protected $fillable = [
        'dn_id',
        'part_no',
        'part_name',
        'group_no',
        'delivery_no',
        'qty',
        'remarks'
    ];

    /**
     * Get the delivery note that owns the detail.
     */
    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class, 'dn_id', 'id');
    }
}
