<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Usuario extends Model
{
    use SoftDeletes;

    protected $table = 'usuarios';
    public $incrementing = true;
    /* Primary key definida por default en "id"*/

    protected $filalable = ['nombre','clave','sector','tipo'];
}

?>