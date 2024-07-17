<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $primaryKey = '_id';
    public $incrementing = false;

    protected $fillable = [
        '_id', 'code', 'name', 'qty', 'location_id', 'organization_id', 'updated_at', 'created_at','variantCode'
    ];

    public function mstLocation()
    {
        return $this->belongsTo(MstLocation::class, 'location_id', '_id');
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class, 'inventory_id', '_id');
    }
    public function plannedInventoryItems()
{
    return $this->hasMany(PlannedInventoryItem::class, 'inventory_id', '_id');
}

}

