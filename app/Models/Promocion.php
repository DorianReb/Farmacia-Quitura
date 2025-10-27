<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promocion extends Model
{
    //
    use SoftDeletes;

    protected $table = 'promociones';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'porcentaje',
        'fecha_inicio',
        'fecha_fin',
        'autorizada_por'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'autorizada_por', 'id');
    }
}
