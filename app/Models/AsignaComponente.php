<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsignaComponente extends Model
{
    use SoftDeletes; // <-- si tu tabla NO tiene deleted_at, quítalo

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

    // Relaciones correctas
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function componente()
    {
        return $this->belongsTo(NombreCientifico::class, 'nombre_cientifico_id');
    }

    public function fuerzaUnidad()
    {
        return $this->belongsTo(UnidadMedida::class, 'fuerza_unidad_id');
    }

    public function baseUnidad()
    {
        return $this->belongsTo(UnidadMedida::class, 'base_unidad_id');
    }
}
