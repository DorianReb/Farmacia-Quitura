<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marca extends Model
{
    //
    use SoftDeletes;

    protected $table = 'marcas';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable=['nombre'];
}
