<?php
namespace App\Controller;

class GeneratorController
{
    public static function GenerarCodigo_5_Caracteres(){
        $permitted_chars = 'ABCDEFGHIJKLMtuvwxyz01234abcdefghijk56789lmnopqrsNOPQRSTUVWXYZ';
        $input_length = strlen($permitted_chars);
        $random_string = '';
        for($i = 0; $i < 5; $i++) {
            $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
        return $random_string;
    }
}