<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $primaryKey = '_id';
    public $incrementing = false;

    protected $fillable = [
        '_id', 'inventory_id', 'serial_number', 'rack', 'rack_type', 'qty', 'status', 'receiving_date', 'refNumber', 'is_out', 'updated_at', 'created_at','vendor_name'
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', '_id');
    }
}

