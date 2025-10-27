<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nivel extends Model
{
    //
    use SoftDeletes;
    protected $table = 'niveles';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'pasillo_id',
        'numero'
    ];

    public function pasillo()
    {
        return $this->belongsTo(Pasillo::class);
    }

    // Accesor para mostrar nombre completo del nivel
    public function getNombreAttribute()
    {
        return $this->pasillo ? $this->pasillo->codigo . ' - Nivel ' . $this->numero : '—';
    }
}
