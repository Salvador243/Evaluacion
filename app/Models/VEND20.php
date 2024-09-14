<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VEND20 extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'VEND20';
    protected $fillable = [
        'CVE_VEND', 'STATUS', 'NOMBRE', 'CLASIFIC', 'CORREOE', 'PV_TEL'
    ];
}
