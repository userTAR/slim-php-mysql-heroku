<?php
namespace App\Controllers;

use DateInterval;
use DateTime;
use DateTimeZone;

class prueba
{
    public function PruebaETA($request, $response, $args)
    {
        $minutos = 80;

        
        $eta = new DateTime('now', new DateTimeZone("America/Argentina/Buenos_Aires"));
        $eta2 = new DateTime('now', new DateTimeZone("Asia/Almaty"));
        
       /*  date_add($eta,date_interval_create_from_date_string($minutos ."minutes")); */

        if($eta > $eta2)
            $resultado = true;
        else
            $resultado = false;

        
        $payload = json_encode(array("mensaje" => $eta, "eta2" => $eta2, "resultado" => $resultado));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}