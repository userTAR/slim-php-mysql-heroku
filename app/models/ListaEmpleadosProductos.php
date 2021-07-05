<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ListaEmpleadosProductos extends Model
{
    public $timestamps = false;

    protected $fillable = ['id_empleado','id_pedido','id_producto_pedido','estado'];
}