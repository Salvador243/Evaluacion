<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POP_WS extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'POP_WS';
    protected $fillable = [
        'ID', 'NOMBRE', 'QUICKLOG', 'SUBCONTRACT', 'CVE_VEND'
    ];

    
}
