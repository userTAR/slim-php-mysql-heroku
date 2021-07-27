<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HistorialLista extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $table = 'historial_productos_empleados';
    public $timestamps = false;

    protected $fillable = [
        'id_empleado','id_pedido','id_producto_pedido','id_estado','id_estado_new','fecha_cambio'
    ];
}