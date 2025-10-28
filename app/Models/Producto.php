<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;


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
    protected $appends = ['imagen_url'];

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function formaFarmaceutica()
    {
        return $this->belongsTo(FormaFarmaceutica::class);
    }

    public function presentacion()
    {
        return $this->belongsTo(Presentacion::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'producto_id');
    }

    public function asignaComponentes()
    {
        return $this->hasMany(AsignaComponente::class, 'producto_id');
    }

    public function asignaUbicaciones()
    {
        return $this->hasMany(AsignaUbicacion::class, 'producto_id');
    }

    public function getImagenUrlAttribute(): string
    {
        // Si hay imagen almacenada, devolver la URL pública (storage link)
        return $this->imagen
            ? Storage::disk('public')->url($this->imagen)  // genera /storage/productos/xxx.jpg
            : asset('images/no-image.png');                // imagen por defecto
    }
}
