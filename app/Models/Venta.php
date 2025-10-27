<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    //
    use SoftDeletes;
    protected $table = 'ventas';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable=[
      'usuario_id',
      'fecha',
      'total'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }
}
