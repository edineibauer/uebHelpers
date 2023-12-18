<?php

/**
 * Check.class [ HELPER ]
 * Classe responável por manipular e validade dados do sistema!
 *
 * @copyright (c) 2017, Edinei J. Bauer
 */


namespace Helpers;

class Check
{

    private static $Data;
    private static $Format;

    /**
     * <b>Verifica E-mail:</b> Executa validação de formato de e-mail. Se for um email válido retorna true, ou retorna false.
     * @param STRING $email = Uma conta de e-mail
     * @return BOOL = True para um email válido, ou false
     */
    public static function email($email)
    {
        return preg_match('/[a-z0-9_\.\-]+@[a-z0-9_\.\-]*[a-z0-9_\.\-]+\.[a-z]{2,4}$/', $email);
    }

    public static function ajax()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }

        return false;
    }

    public static function isJson($string)
    {
        if (is_string($string)) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        } else {
            return false;
        }
    }

    public static function codificacao($term)
    {
        $term = Helper::replaceCharsetToUtf8($term);
        $content_back = $term;
        $i = -1;
        while (preg_match('/(\&amp;lt;|\&amp;|\&lt;|\&gt;)/i', $term) && $i < 8):
            $term = $content_back;
            $i++;

            if ($i === 0):
                $term = htmlspecialchars($term);
            elseif ($i === 1):
                $term = htmlentities($term);
            elseif ($i === 2):
                $term = htmlspecialchars(htmlentities($term));
            elseif ($i === 3):
                $term = html_entity_decode($term);
            elseif ($i === 4):
                $term = htmlspecialchars_decode($term);
            elseif ($i === 5):
                $term = htmlspecialchars_decode(html_entity_decode($term));
            elseif ($i === 6):
                $term = htmlentities(htmlspecialchars($term));
            elseif ($i === 7):
                $term = html_entity_decode(htmlspecialchars_decode($term));
            endif;

            $term = (strlen($term) < 3 ? $content_back : $term);
        endwhile;

        return ($i === 8 ? false : strip_tags($term));
    }

    /**
     * @param string $val
     * @param string $mask
     * @return string
     */
    public static function mask(string $val, string $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k]))
                    $maskared .= $val[$k++];
            } else {
                if (isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }

    public static function name(string $name = null, array $escape = [])
    {
        $f = array();
        $f['a'] = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr|"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª¹²³£¢¬™®★’`§☆●•…”“’‘♥♡■◎≈◉';
        $f['b'] = "aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                                            ";

        //escape some chars
        if ($escape)
            $f['a'] = str_replace($escape, "", $f['a']);

        $data = strtr(utf8_decode($name), utf8_decode($f['a']), $f['b']);
        $data = strip_tags(trim($data));
        $data = str_replace(' ', '-', $data);
        $data = str_replace(array('-----', '----', '---', '--'), '-', $data);

        return str_replace('?', '-', utf8_decode(strtolower(utf8_encode($data))));
    }

    /**
     * Valida CNPJ
     *
     * @param string $cnpj
     * @return bool
     */
    public static function cnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string)$cnpj);

        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;

        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
            return false;

        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }

    /**
     * Valida CPF
     *
     * @param string $cpf
     * @return bool
     */
    public static function cpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', (string)$cpf);
        if (strlen($cpf) !== 11 || $cpf === '00000000000' || $cpf === '11111111111' || $cpf === '22222222222' || $cpf === '33333333333' || $cpf === '44444444444' || $cpf === '55555555555' || $cpf === '66666666666' || $cpf === '77777777777' || $cpf === '88888888888' || $cpf === '99999999999'):
            return false;
        endif;

        for ($i = 0, $j = 10, $soma = 0; $i < 9; $i++, $j--):
            $soma += $cpf[$i] * $j;
        endfor;

        $resto = $soma % 11;
        if ($cpf[9] != ($resto < 2 ? 0 : 11 - $resto)):
            return false;
        endif;

        for ($i = 0, $j = 11, $soma = 0; $i < 10; $i++, $j--):
            $soma += $cpf[$i] * $j;
        endfor;

        $resto = $soma % 11;
        return $cpf[10] == ($resto < 2 ? 0 : 11 - $resto);
    }

    /**
     * Retorna verdadeiro se o array for associativo do tipo ["nome" => "Edinei"]
     *
     * @param array $arr
     * @return bool
     */
    public static function isAssoc(array $arr): bool
    {
        if (array() === $arr)
            return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * <b>Verifica se o endereço passado é de uma imagem:</b>
     *
     * @param STRING $img = url da imagem
     * @return BOOL
     */
    public static function image($url)
    {
        $javascript_loop = 0;
        $timeout = 15;
        $url = str_replace("&amp;", "&", urldecode(trim($url)));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0");
        curl_setopt($ch, CURLOPT_REFERER, $url);

        curl_setopt($ch, CURLOPT_COOKIEJAR, tempnam("/tmp", "CURLCOOKIE"));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate, br");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        curl_close($ch);

        if ($response['http_code'] == 301 || $response['http_code'] == 302) {
            ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");

            if ($headers = get_headers($response['url'])) {
                foreach ($headers as $value) {
                    if (substr(strtolower($value), 0, 9) == "location:")
                        return get_url(trim(substr($value, 9, strlen($value))));
                }
            }
        }

        if ($response['http_code'] !== 200 || ((preg_match("/>[[:space:]]+window\.location\.replace\('(.*)'\)/i", $content, $value) || preg_match("/>[[:space:]]+window\.location\=\"(.*)\"/i", $content, $value)) && $javascript_loop < 5)) {
            return false;
        } elseif (!preg_match('/image/i', $response['content_type']) || empty($content) || $content == '') {
            return false;
        }

        return true;
    }

    /**
     * @param string $senha
     * @return string
     */
    public static function password(string $senha): string
    {
        return md5(str_replace(['1', 'c', 's', '2', 'r', 'o', 'n', 'l', 'f', 'x', '0', 'k', 'v', '5', 'y'], ['b', '4', '9', '6', 'w', 'a', 'd', '3', 'z', '7', 'j', 'm', '8', 'h', 't'], md5("t" . trim($senha) . "0!")));
    }

    /**
     * @param string $image
     * @return string
     */
    public static function getImage(string $image): string
    {
        return HOME . str_replace('\\', '/', json_decode($image, true)[0]['url']);
    }

    public static function json($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * <b>Tranforma Data:</b> Transforma uma data no formato DD/MM/YY em uma data no formato TIMESTAMP!
     * @param STRING $Name = Data em (d/m/Y) ou (d/m/Y H:i:s)
     * @return STRING = $Data = Data no formato timestamp!
     */
    public static function data($Data, $mode = 2)
    {
        self::$Format = explode(' ', $Data);
        self::$Data = explode('-', self::$Format[0]);
        $a = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        $b = array("jan", "fev", "mar", "abr", "mai", "jun", "jul", "ago", "set", "out", "nov", "dez");

        if (!empty(self::$Format[1])):
            $d = explode(':', self::$Format[1]);
        else:
            $d[0] = 0;
        endif;

        if (isset(self::$Data[2])):
            if ($mode && $mode === 2):
                return self::$Data[2] . ' ' . str_replace($a, $b, self::$Data[1]);
            else:
                return self::$Data[2] . ' ' . str_replace($a, $b, self::$Data[1]) . ' ' . self::$Data[0];
            endif;
        endif;

        return "";
    }

    /**
     * <b>Limita os Palavras:</b> Limita a quantidade de palavras a serem exibidas em uma string!
     *
     * @param STRING $string = Uma string qualquer
     * @return STRING = $Limite = String limitada pelo $Limite
     */
    public static function words($string, $limite = 20, $pointer = null)
    {
        $string = strip_tags(trim($string));

        $arrWords = explode(' ', $string);
        $newWords = implode(' ', array_slice($arrWords, 0, $limite));

        $pointer = (empty($pointer) ? '...' : ' ' . $pointer);
        $result = ($limite < count($arrWords) ? $newWords . $pointer : $string);

        return $result;
    }
}
