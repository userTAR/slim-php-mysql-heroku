<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EstadoUsuarios extends Model
{
    public $timestamps = false;
    protected $table = 'estadomesas';

    protected $fillable = [
        'estado'
    ];
}