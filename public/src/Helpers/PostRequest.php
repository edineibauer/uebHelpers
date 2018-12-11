<?php
/**
 * esta classe será responsável por enviar um post request para outro site
 * com parâmetros
 *
 * @author Edinei J. Bauer 2017 <edineibauer@gmail.com>
 *
 */

namespace Helpers;

class PostRequest {

    private $url;
    private $dados;
    private $result;
    private $status;
    private $retorno;

    function __construct($url = null, $dados = null) {
        if ($url):
            $this->setUrl($url);

            if ($dados && is_array($dados)):
                $this->dados = $dados;
            endif;
        endif;
    }

    public function setUrl($url) {
        $this->url = (string) str_replace("&amp;", "&", urldecode($url));
    }

    /*
     * passa dados para a requisição post
     */

    public function setDados($campo, $valor) {
        $this->dados[$campo] = $valor;
    }

    /*
     * Retorna a resposta obtida na requisição
     */

    public function getResult() {
        return $this->result;
    }

    /*
     * Retorna os valores obtidos na requisição
     */

    public function getRetorno() {
        if ($this->retorno):
            return $this->retorno;
        else:
            return false;
        endif;
    }

    /*
     * prepara o envio das requisições
     */

    public function exeEnvio() {
        if ($this->checkStatus()):

            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($this->dados)
                )
            );

            $this->connectToTarget(stream_context_create($options));

        else:
            $this->result = "Status do site '{$this->status}'. Não foi possível se comunicar.";
        endif;
    }

    /*
     * Faz o envio do post
     * guarda informações de retorno caso haja
     */

    private function connectToTarget($context) {
        $result = file_get_contents($this->url, false, $context);

        if ($result === FALSE):

            $this->result = "Valores Enviados, não houve retorno.";

        else:

            $this->retorno = $result;
            $this->result = "Valores Enviados, valores retornados.";

        endif;
    }

    /*
     * Verifica se o link alvo esta acessível
     */

    private function checkStatus() {

        $teste_conexao = Check::file_get($this->url);
        if ($teste_conexao[1]['http_code'] === 200):

            return true;
        else:
            $this->status = $teste_conexao[1]['http_code'];
        endif;

        return false;
    }

}
