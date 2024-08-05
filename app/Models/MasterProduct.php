<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterProduct extends Model
{
    use HasFactory;

    protected $table = 'master_products';

    protected $fillable = [
        '_id', 'code', 'name', 'levelmin', 'levelmax', 'has_sn', 'attributes', 'no_case', 'variantCode',
        'dest_delivery', 'prod_process', 'part_no', 'group_no', 'g_number', 'model', 'cutting_center',
        'press_destination', 'tags', 'length', 'width', 'height', 'weight', 'color', 'default_sn_formula',
        'default_unit', 'organization_id', 'updated_at', 'created_at'
    ];

    protected $casts = [
        'attributes' => 'array',
        'tags' => 'array'
    ];
}
