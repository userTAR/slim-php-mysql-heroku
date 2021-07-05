<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EstadoPedidos extends Model
{
    public $timestamps = false;
    protected $table = 'estadopedidos';

    protected $fillable = [
        'estado'
    ];
}