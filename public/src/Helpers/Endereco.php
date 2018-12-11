<?php
/**
 * Created by PhpStorm.
 * User: nenab
 * Date: 13/02/2018
 * Time: 20:57
 */

namespace Helpers;


class Endereco
{
    public static function findCep(string $cep) {
        return self::baseApi('cep=' . $cep);
    }

    public static function findPosition(string $lat, string $long)
    {
        return self::baseApi("lat={$lat}&lng={$long}");
    }

    public static function findEndereco(string $estado, string $cidade, string $bairro = "")
    {
        return self::baseApi("estado={$estado}&cidade={$cidade}" . !empty($bairro) ? "&bairro={$bairro}" : "");
    }

    public static function findCidades(string $estado)
    {
        return self::baseApi("estado={$estado}", "cities");
    }

    private static function baseApi(string $param, string $ceps = "ceps")
    {
        if(defined('CEPABERTO') && !empty(CEPABERTO)) {
            if(isset($_COOKIE['cepaberto']))
                sleep(3);

            setcookie("cepaberto", 1, time() + 3, "/");
            $token = CEPABERTO;
            $url = 'http://www.cepaberto.com/api/v2/' . $ceps . '.json?' . $param;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token token="' . $token . '"'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            return json_decode(curl_exec($ch), true);
        }
        return null;
    }
}