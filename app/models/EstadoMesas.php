<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EstadoMesas extends Model
{
    public $timestamps = false;
    protected $table = 'estadomesas';

    protected $fillable = [
        'estado'
    ];
}