<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TipoProducto extends Model
{
    protected $table = 'tipoproductos';
    public $timestamps = false;

    protected $fillable = ['tipo'];
}