<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NombreCientifico extends Model
{
    //
    use SoftDeletes;
    protected $table = 'nombres_cientificos';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable=['nombre'];
}
