<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    /* use SoftDeletes; */
    public $timestamps = false;
    protected $fillable = ['codigo','codigo_mesa','id_cliente','id_mozo','lista','estado','eta'];
    

}