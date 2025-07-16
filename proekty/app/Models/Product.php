<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable =
    [
        'registration_date',
        'project_number',
        'egrz_number',
        'project_name',
        'object_type',
        'functional_purpose',
        'service_type',
        'start_date',
        'name',
        'inn',
        'contract_date',
        'conclusion_date',
        'conclusion_result',
        'project_status',
        'cost_declared',
        'cost_adjusted',
        'stage_construction_works'
    ];
}
