<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    //
    use SoftDeletes;
    protected $table = 'productos';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'marca_id',
        'forma_farmaceutica_id',
        'presentacion_id',
        'unidad_medida_id',
        'categoria_id',
        'nombre_comercial',
        'descripcion',
        'contenido',
        'requiere_receta',
        'stock_minimo',
        'precio_venta',
        'existencias',
        'codigo_barras',
        'imagen',
        'alt_imagen'
    ];
}
