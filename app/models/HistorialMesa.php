<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HistorialMesa extends Model
{
    public $incrementing = true;
    protected $primaryKey = 'id';
    protected $table = 'historialmesas';
    protected $fillable = [
        'id_mesa','id_estado','id_estado_new','fecha_cambio'
    ];
}