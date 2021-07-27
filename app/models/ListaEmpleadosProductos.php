<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ListaEmpleadosProductos extends Model
{
    public $timestamps = false;
    protected $table = 'lista_productos_empleados';

    protected $fillable = ['id_empleado','id_pedido','id_producto_pedido','estado'];
}