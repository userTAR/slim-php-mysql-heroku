<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HistorialUsuarios extends Model
{
    public $incrementing = true;
    protected $primaryKey = 'id';
    protected $table = 'historialusuarios';
    public $timestamps = false;
    protected $fillable = [
        'id_usuario','id_estado','id_estado_new','fecha_cambio'
    ];
}