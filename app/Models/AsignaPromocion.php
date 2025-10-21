<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsignaPromocion extends Model
{
    //
    use SoftDeletes;
    protected $table = 'asigna_promociones';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'promocion_id',
        'lote_id',
    ];
}
