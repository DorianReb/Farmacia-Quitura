<?php

namespace App\Models;


use App\Models\Promocion;
use App\Models\Lote;
use Illuminate\Http\Request;
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

    // Relación con la promoción
    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'promocion_id', 'id')->withTrashed();
    }

    // Relación con el lote/producto
    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id', 'id')->withTrashed();
    }
}
