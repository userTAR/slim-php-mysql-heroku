<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EstadoUsuarios extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $table = 'estadousuarios';
    public $timestamps = false;

    protected $fillable = [
        'id','estado'
    ];
}