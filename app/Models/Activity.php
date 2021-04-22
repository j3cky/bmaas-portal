<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    protected $fillable =[
        'uuid','tenant_id','action','start_time','message'

    ];

    protected $hidden =[
    ];//

    protected $table = 'activity_audit';
}
