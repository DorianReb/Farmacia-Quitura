<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleVenta extends Model
{
    //
    use SoftDeletes;
    protected $table = 'detalles_ventas';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'venta_id',
        'lote_id',
        'cantidad',
        'subtotal'
    ];
}
