<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormaFarmaceutica extends Model
{
    //
    use SoftDeletes;
    protected $table = 'formas_farmaceuticas';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'nombre',
    ];
}
