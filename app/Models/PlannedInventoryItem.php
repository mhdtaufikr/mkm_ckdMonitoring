<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlannedInventoryItem extends Model
{
    use HasFactory;

    protected $primaryKey = '_id';
    public $incrementing = false;

    protected $fillable = [
        '_id', 'inventory_id', 'planned_receiving_date', 'planned_qty', 'status','vendor_name'
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', '_id');
    }
}

