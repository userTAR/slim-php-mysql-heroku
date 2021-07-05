<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerfilUsuario extends Model
{

    protected $primaryKey = 'id';
    protected $table = 'perfilesusuarios';
    public $incrementing = true;


    protected $fillable = [
        'id','perfil'
    ];
}

?>