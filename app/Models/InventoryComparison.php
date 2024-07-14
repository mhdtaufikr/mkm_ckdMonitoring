<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryComparison extends Model
{
    use HasFactory;

    // Set the table name
    protected $table = 'inventory_comparison';

    // Set the primary key
    protected $primaryKey = 'planned_item_id';

    // Set incrementing to false since the primary key is not an integer
    public $incrementing = false;

    // Disable timestamps
    public $timestamps = false;

    // Define the fillable attributes
    protected $fillable = [
        'planned_item_id',
        'inventory_id',
        'item_code',
        'planned_receiving_date',
        'planned_qty',
        'received_qty',
        'receiving_date',
        'comparison_status',
    ];

    // Set the date attributes to be cast to Carbon instances
    protected $dates = [
        'planned_receiving_date',
        'receiving_date',
    ];
}
