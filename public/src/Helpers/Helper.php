<?php

/**
 * Helper.class [ HELPERS ]
 * Classe responável por ajudar em atividades de desenvolvimento
 *
 * @copyright (c) 2017, Edinei J. Bauer
 */

namespace Helpers;

class Helper
{

    /**
     * retorna cor auxiliar inversa em hexadecimal
     * @param STRING $name = hexadecimal color
     * @return STRING = cor de resalte à $name
     */
    public static function corAuxiliar($name)
    {
        $style = str_replace("#", "", $name);
        $style = strlen($style) === 3 ? $style[0] . $style[0] . $style[1] . $style[1] . $style[2] . $style[2] : $style;

        $todo = hexdec($style[0] . $style[1]) + hexdec($style[2] . $style[3]) + hexdec($style[4] . $style[5]) - 550;

        if ($todo > 0): //cor clara
            $base = round($todo / 60);
            return "#{$base}{$base}{$base}";
        else: //cor escura
            return "#FFF";
        endif;
    }

    /**
     * Copia pasta inteira para o destino
     * @param string $src
     * @param string $dst
     */
    public static function recurseCopy(string $src, string $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) )
                    Helper::recurseCopy($src . '/' . $file,$dst . '/' . $file);
                else
                    copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
        closedir($dir);
    }

    /**
     * Exclui pasta inteira
     * @param string $src
     */
    public static function recurseDelete(string $src) {
        if(file_exists($src) && is_dir($src)) {
            $dir = opendir($src);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src . '/' . $file))
                        Helper::recurseDelete($src . '/' . $file);
                    else
                        unlink($src . '/' . $file);
                }
            }
            closedir($dir);
            rmdir($src);
        }
    }

    /**
     * Copia pasta inteira para o destino
     * @param string $filename
     * @param string $dst
     */
    public static function ZipFiles(string $filename, string $dst)
    {
        $zip = new \ZipArchive();
        $zip->open($filename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        if(is_dir($dst)) {

            // Create recursive directory iterator
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dst),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($files as $name => $file)
            {

                $filePath = $file->getRealPath();
                $relativePath = str_replace('\\', '/', substr($filePath, strlen($dst) + 1));

                if(in_array(substr($file, strrpos($file, '/')+1), ['.', '..']))
                    continue;

                if (is_dir($file)) {
                    $zip->addEmptyDir($relativePath);
                } else if (is_file($file)) {
                    $zip->addFile($filePath, $relativePath);
                }
            }
        } else {
            $zip->addFile($dst, pathinfo($dst, PATHINFO_BASENAME));
        }

        $zip->close();
    }

    /**
     * Verifica se o link esta online
     * @param string $url
     * @return bool
     */
    public static function isOnline(string $url)
    {
        $agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";$ch=curl_init();
        curl_setopt ($ch, CURLOPT_URL,$url );
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch,CURLOPT_VERBOSE,false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch,CURLOPT_SSLVERSION,3);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE);
        $page = curl_exec($ch);

        var_dump($page);
        die;

        //echo curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpcode>=200 && $httpcode<300);
    }

    /**
     * Convert imagem recebida em formato json ou array armazenada pelo sistema da ontab
     * array ou json format [["url" => "link", "size" => 335]]
     *
     * @param mixed $json
     * @return string
     */
    public static function convertImageJson($json): string
    {
        if (empty($json))
            return "";
        elseif (is_array($json) && !empty($json[0]['url']))
            return HOME . str_replace('\\', '/', $json[0]['url']);
        elseif (Check::isJson($json) && preg_match('/url/i', $json))
            return HOME . str_replace('\\', '/', json_decode($json, true)[0]['url']);

        return "";
    }

    /**
     * Converte cor hexadecimal para RGB ->retorna array('red' => 255, 'green' => 112, 'blue' => 114)
     * @param string $colour
     * @return mixed
     */
    public static function hex2rgb(string $colour)
    {
        if ($colour[0] == '#')
            $colour = substr($colour, 1);

        if (strlen($colour) == 6)
            list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
        elseif (strlen($colour) == 3)
            list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
        else
            return false;

        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array('red' => $r, 'green' => $g, 'blue' => $b);
    }

    /**
     * Converte os valores no tipo string de um array nos tipos corretos dos valores
     * ex: convert "true" => true; "12" => 12
     *
     * @param array $array
     * @return array
     */
    public static function convertStringToValueArray(array $array): array
    {

        foreach ($array as $i => $attr)
            $array[$i] = (is_array($attr) ? self::convertStringToValueArray($attr) : self::convertStringToValues($attr));

        return $array;
    }

    /**
     * Converte os valores no tipo string para os tipos corretos dos valores
     * ex: convert "true" => true; "12" => 12
     *
     * @param mixed $array
     * @return mixed
     */
    public static function convertStringToValues($st)
    {
        if ($st === "TRUE" || $st === "true" || $st === "false" || $st === "FALSE")
            return $st === "TRUE" || $st === "true";
        elseif (is_numeric($st) && preg_match('/\./i', $st))
            return (float)$st;
        elseif (is_numeric($st))
            return (int)$st;

        return $st;
    }

    public static function getArrayData($array, $value)
    {
        $dado = array();
        if (is_array($array)) {
            if (count($array) === 9)
                $dado[$array[0]][$array[1]][$array[2]][$array[3]][$array[4]][$array[5]][$array[6]][$array[7]][$array[8]] = $value;
            elseif (count($array) === 8)
                $dado[$array[0]][$array[1]][$array[2]][$array[3]][$array[4]][$array[5]][$array[6]][$array[7]] = $value;
            elseif (count($array) === 7)
                $dado[$array[0]][$array[1]][$array[2]][$array[3]][$array[4]][$array[5]][$array[6]] = $value;
            elseif (count($array) === 6)
                $dado[$array[0]][$array[1]][$array[2]][$array[3]][$array[4]][$array[5]] = $value;
            elseif (count($array) === 5)
                $dado[$array[0]][$array[1]][$array[2]][$array[3]][$array[4]] = $value;
            elseif (count($array) === 4)
                $dado[$array[0]][$array[1]][$array[2]][$array[3]] = $value;
            elseif (count($array) === 3)
                $dado[$array[0]][$array[1]][$array[2]] = $value;
            elseif (count($array) === 2)
                $dado[$array[0]][$array[1]] = $value;
            elseif (count($array) === 1)
                $dado[$array[0]] = $value;

        } else {
            $dado[$array] = $value;
        }

        return $dado;
    }

    /**
     * <b>replaceString:</b> troca uma expressão regular por um valor
     * @param STRING $from = expressão a ser correspondida
     * @param STRING $to = string substituta
     * @param STRING $subject = string a ser mudada
     * @return STRING = $subject com substituições aplicadas
     */
    public static function replaceString($from, $to, $subject, $limit = 100000)
    {
        $from = '/' . preg_quote($from, '/') . '/';
        return preg_replace($from, $to, $subject, $limit);
    }

    /**
     * <b>Corrige url abstrata:</b> links são fornecidos no html muitas vezes de forma abstrata e outras com link completo, padroniza na forma completa
     *
     * @param STRING $url = a url a padronizar
     * @param $domain STRING = dominio de origem da url
     * @return $url STRING
     */
    public static function replaceUrlAbstractToFullUrl($url, $domain = null)
    {

        if ($domain):
            $domain = (preg_match('/\/$/i', $domain) ? substr($domain, 0, -1) : $domain);
            $domain = str_replace(array("https://", "http://"), "", $domain);
            $domains = explode("/", $domain);
            $domain = "http://" . $domains[0];
            unset($domains[count($domains) - 1], $domains[0]);
            $count = count($domains);
        endif;

        if (preg_match('/^\/\//', $url) || preg_match('/^www\./', $url)):
            $url = 'http:' . (preg_match('/^www\./', $url) ? "//" : "") . $url;

        elseif ((preg_match('/^\//', $url) || preg_match('/^(\w|-)/i', $url)) && !preg_match('/^http:/i', $url) && $domain):
            $url = $domain . (!preg_match('/^\//', $url) ? "/" : "") . $url;

        elseif (preg_match('/^\.\./', $url) && $domain):
            if (preg_match_all('/\.\.\//i', $url, $matches) > 0):
                foreach ($matches[0] as $i => $t):
                    unset($domains[$count - $i]);
                endforeach;
            endif;

            $url = $domain . (!empty($domains) ? '/' . implode("/", $domains) : "") . '/' . str_replace('../', '', $url);

        elseif (preg_match('/^\./', $url) && $domain):
            $url = $domain . (isset($domains[1]) ? "/" . $domains[1] : "") . (isset($domains[2]) ? "/" . $domains[2] : "") . (isset($domains[3]) ? "/" . $domains[3] : "") . str_replace('./', "/", $url);
        endif;

        $testeHttp = explode("http", $url);
        if (isset($testeHttp[2])):
            $url = "http" . $testeHttp[count($testeHttp) - 1];
        endif;
        return $url;
    }

    public static function replaceCharsetToUtf8($source)
    {
        // detect the character encoding of the incoming file
        $encoding = mb_detect_encoding($source, "auto");

        // escape all of the question marks so we can remove artifacts from
        // the unicode conversion process
        $target = str_replace("?", "[question_mark]", $source);

        // convert the string to the target encoding
        $target = mb_convert_encoding($target, "UTF-8", $encoding);

        // remove any question marks that have been introduced because of illegal characters
        $target = str_replace("?", "", $target);

        // replace the token string "[question_mark]" with the symbol "?"
        $target = str_replace("[question_mark]", "?", $target);

        return $target;
    }

    /**
     * <b>listFolder:</b> Lista os arquivos e pastas de uma pasta.
     * @param STRING $dir = nome do diretório a ser varrido
     * @return array $directory = lista com cada arquivo e pasta no diretório
     */
    public static function listFolder($dir, $limit = 5000)
    {
        $directory = array();
        if (file_exists($dir)) {
            $i = 0;
            foreach (scandir($dir) as $b):
                if ($b !== "." && $b !== ".." && $i < $limit):
                    $directory[] = $b;
                    $i++;
                endif;
            endforeach;
        }

        return $directory;
    }

    /**
     * @param $string STRING = string em camelCase ou com underline
     * @return string STRING = com espaços em vez de camelCase
     *
     */
    public static function replaceVarNameToNameWithSpace($string)
    {
        $temp = "";
        $before = "";
        for ($i = 0; $i < strlen($string); $i++) {

            if (preg_match('/[A-Z]/', $string[$i]) && preg_match('/[a-z]/', $before)) {
                $word[] = $temp;
                var_dump($temp);
                $temp = $string[$i];
            } else {
                $temp .= $string[$i];
            }
            $before = $string[$i];
        }

        $word[] = $temp;
        $string = implode('-', $word);

        $a = array(' ', '_', '-', '  ', '   ');
        $b = array('-', '-', ' ', ' ', ' ');
        return ucwords(trim(str_replace($a, $b, $string)));
    }

    /**
     * <b>Obtem IP real:</b> obtem o IP real do usuário que esta acessando
     * @return STRING = IP de origem do acesso
     */
    public static function getIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])):
            return $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])):
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        endif;

        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Envia um POST REQUEST a uma url com data
     * obtem a resposta
     *
     * @param $url
     * @param $data
     * @return STRING
     */
    public static function postRequest($url, $data)
    {
        $url = str_replace("&amp;", "&", urldecode(trim($url)));

        $options = array('http' => array('header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method' => 'POST', 'content' => http_build_query($data)));
        $context = stream_context_create($options);

        try {
            $data = @file_get_contents($url, false, $context);
            return $data;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * <b>Obtem dados do link:</b> obtem os dados do link informado
     * @param STRING $url = url do site requisitado
     * @return ARRAY
     */
    public static function getFileContent($url)
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

        if ((preg_match("/>[[:space:]]+window\.location\.replace\('(.*)'\)/i", $content, $value) || preg_match("/>[[:space:]]+window\.location\=\"(.*)\"/i", $content, $value)) && $javascript_loop < 5) {
            return get_url($value[1], $javascript_loop + 1);
        } else {
            if ($response['http_code'] === 200) {
                return $content;
            } else {
                return "código de resposta {$response['http_code']}";
            }
        }
    }

    /**
     * <b>Imagem Upload:</b> Ao executar este HELPER, ele automaticamente verifica a existencia da imagem na pasta
     * uploads. Se existir retorna a imagem redimensionada!
     * @return HTML = imagem redimencionada!
     */
    public static function getImage($ImageUrl, $ImageDesc, $ImageW = null, $ImageH = null)
    {
        self::$Data = $ImageUrl;

        if (file_exists(self::$Data) && !is_dir(self::$Data)):
            $patch = HOME;
            $imagem = self::$Data;
            return "<img src=\"{$patch}/tim.php?src={$patch}/{$imagem}&w={$ImageW}&h={$ImageH}\" alt=\"{$ImageDesc}\" title=\"{$ImageDesc}\"/>";
        elseif (file_exists('../' . self::$Data) && !is_dir('../' . self::$Data)):
            $patch = HOME;
            $imagem = self::$Data;
            return "<img src=\"{$patch}/tim.php?src={$patch}/{$imagem}&w={$ImageW}&h={$ImageH}\" alt=\"{$ImageDesc}\" title=\"{$ImageDesc}\" class='imgreturn' />";
        else:
            return false;
        endif;
    }

    public static function createFolderIfNoExist($folder)
    {
        if (!file_exists($folder) && !is_dir($folder)) {
            if (!mkdir($folder, 0775, true)) {
                echo "Erro ao criar o diretório: $folder";
            } else {
                chmod($folder, 0775);
            }
        }
    }


    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function arrayMerge(array &$array1, array &$array2): array
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
                $merged[$key] = self::arrayMerge($merged[$key], $value);
            else
                $merged[$key] = $value;
        }
        return $merged;
    }
}
