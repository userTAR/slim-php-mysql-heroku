<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    public $timestamps = false;
    protected $fillable = ['codigo','sector','id_estado'];

}