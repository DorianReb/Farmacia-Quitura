<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsignaUbicacion extends Model
{
    //
    use SoftDeletes;
    protected $table = 'asigna_ubicaciones';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'producto_id',
        'nivel_id'
    ];
}
