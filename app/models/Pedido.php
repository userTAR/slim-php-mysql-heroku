<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    public $timestamps = false;
    protected $fillable = ['codigo','codigo_mesa','id_cliente','id_mozo','lista','factura','estado','pedido_time','eta','entrega_time'];
    

}