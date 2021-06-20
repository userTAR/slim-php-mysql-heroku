<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Producto extends Model
{
    /* use SoftDeletes; */

    /* Nombre de tabla "productos" por defecto */
    /* Primary key definida por default en "id"*/
    /* Id autoincremental seteado true por default */
    public $timestamps = false;

    protected $fillable = ['nombre','tipo'];
}