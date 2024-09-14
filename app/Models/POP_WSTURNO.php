<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POP_WSTURNO extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'ID';
    protected $table = 'POP_WSTURNO';
    
    protected $fillable = [
        'ID',
        'WS_ID',
        'DIASEM',
        'INICIO',
        'FIN',
        'TIPO_RESP',
        'CVE_RESP',
        'CVE_DEP',
    ];


    protected $cast = [
        'TIPO_RESP' => 'string',
    ];
}
