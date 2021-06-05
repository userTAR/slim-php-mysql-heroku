<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model
{
    use SoftDeletes;

    /* Nombre de tabla "mesas" por defecto */
    /* Primary key definida por default en "id"*/
    /* Id autoincremental seteado true por default */

    protected $filalable = ['sector'];

}