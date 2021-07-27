<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

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