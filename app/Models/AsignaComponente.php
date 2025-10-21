<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsignaComponente extends Model
{
    //
    use SoftDeletes;
    protected $table = 'asigna_componentes';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'producto_id',
        'nombre_cientifico_id',
        'fuerza_cantidad',
        'fuerza_unidad_id',
        'base_cantidad',
        'base_unidad_id',
    ];
}
