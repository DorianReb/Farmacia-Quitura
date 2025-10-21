<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lote extends Model
{
    //
    use SoftDeletes;
    protected $table = 'lotes';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'producto_id',
        'codigo',
        'fecha_caducidad',
        'precio_compra',
        'cantidad',
        'fecha_entrada',
        'usuario_id'
    ];
}
