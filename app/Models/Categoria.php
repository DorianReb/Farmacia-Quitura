<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    //
use SoftDeletes;

protected $table = 'categorias';
protected $primaryKey = 'id';
public $timestamps = true;
protected $fillable = [
    'nombre'
];
}
