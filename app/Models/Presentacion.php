<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Presentacion extends Model
{
    //
    use SoftDeletes;
    protected $table = 'presentaciones';
    protected $primaryKey = 'id';
    public $timestamps=true;
    protected $fillable=['nombre'];
}
