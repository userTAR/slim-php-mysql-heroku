<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    public $timestamps = false;
    protected $table = 'encuestas';

    protected $fillable = [
        'codigo_pedido','mesa','restaurante','mozo','cocina','resumen'
    ];
}