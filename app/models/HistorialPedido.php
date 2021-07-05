<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HistorialPedido extends Model
{
    public $incrementing = true;
    protected $primaryKey = 'id';
    protected $table = 'historialpedidos';
    protected $fillable = [
        'id_pedido','id_estado','id_estado_new','fecha_cambio'
    ];
}