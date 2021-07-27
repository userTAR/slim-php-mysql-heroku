<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $table = 'usuarios';
    public $timestamps = false;

    protected $fillable = [
        'nombre','clave','sector','tipo','estado_id','alta','baja'
    ];
}

?>