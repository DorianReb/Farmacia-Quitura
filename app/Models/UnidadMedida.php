<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadMedida extends Model
{
    //
    use softDeletes;
    protected $table='unidades_medida';
    protected $primaryKey='id';
    public $timestamps=true;
    protected $fillable=[
        'nombre'
    ];
}
