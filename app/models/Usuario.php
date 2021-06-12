<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Usuario extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $table = 'usuarios';
    protected $deleted_at = 'baja';
    public $incrementing = true;
    public $timestamps = false;


    protected $fillable = [
        'nombre','clave','sector','tipo','estado_id','alta','baja'
    ];
}

?>