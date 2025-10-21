<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pasillo extends Model
{
    //
    use SoftDeletes;
    protected $table='pasillos';
    protected $primaryKey='id';
    public $timestamps=true;
    protected $fillable=['codigo'];
}
