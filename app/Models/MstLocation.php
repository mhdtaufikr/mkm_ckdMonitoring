<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstLocation extends Model
{
    use HasFactory;

    protected $primaryKey = '_id';
    public $incrementing = false;

    protected $fillable = [
        '_id', 'name', 'code', 'address', 'phone', 'location_type', 'lat', 'lng'
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'location_id', '_id');
    }
}

