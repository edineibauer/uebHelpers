<?php

namespace Helpers;

class Time
{
    private $data;
    private $certeza = array("h" => false, "i" => false, "s" => false);
    private $erro;

    /**
     * Recebe um time em formato aleatório e retorna um time no padrão informado ou que esta por padrão setado nesta classe
     * @param string $time
     * @param string $pattern
     * @return mixed
     */
    public function getTime(string $time, string $pattern = "H:i:s")
    {
        if (!$time) {
            $this->data = date($pattern);
        } else {
            $this->prepareTime($time);
        }

        if ($this->erro) {
            return null;

        } else {
            $strtoTime = "";
            if (isset($this->data['h']) && $this->data['h'] > 11) {
                $this->data['h'] -= 12;
                $strtoTime = "+12hours";
            }

            return date($pattern, strtotime(($this->data['h'] ?? 0) . ":" . ($this->data['i'] ?? 0) . ":" . ($this->data['s'] ?? 0) . $strtoTime));
        }
    }

    /**
     * @param mixed $erro
     */
    public function setErro($erro)
    {
        $this->erro = $erro;
    }

    /**
     * @return string
     */
    public function getErro(): string
    {
        return $this->erro;
    }

    /**
     * Particiona o time recebido para analisar qual informação diz respeito à hora, minutos e segundos
     * @param string $times
     */
    private function prepareTime(string $times)
    {
        foreach (preg_split('/\W/i', $times) as $time) {
            $this->getDatePart($time);
        }
    }

    /**
     * Verifica dados básicos da informação, e manda para verificações mais precisas
     * @param string $dado
     */
    private function getDatePart(string $dado)
    {
        $dado = strip_tags(trim($dado));
        if (!empty($dado)) {

            if (!is_numeric($dado)) {
                $this->checkNameHour((string)$dado);

            } else {
                $this->data[$this->checkValueTime($dado)] = (int)$dado;
            }
        }
    }

    /**
     * Verifica qual tempo utilizar
     * @param int $value
     * @return mixed
    */
    private function checkValueTime(int $value)
    {
        if ($value > -1 && $value < 60) {
            if (!isset($this->data['h']) && $value < 24) {
                return "h";
            } elseif (!isset($this->data['i'])) {
                return "i";
            } else {
                return "s";
            }
        }

        return null;
    }

    /**
     * Verifica Se a string encontrada diz respeito a uma hora, minuto ou segundo
     * @param string $time
     */
    private function checkNameHour(string $time)
    {
        if (preg_match('/\s*\d{1,2}\s*(h|hour|hora|hs|hr|hrs)\s*/i', $time)) {
            $this->setCerteza("h", $time);
        }

        if (preg_match('/\s*\d{1,2}\s*(m|min|minuto)\s*/i', $time)) {
            $this->setCerteza("i", $time);
        }

        if (preg_match('/\s*\d{1,2}\s*(s|seg|segundo)\s*/i', $time)) {
            $this->setCerteza("s", $time);
        }
    }

    /**
     * seta certeza a um campo do time
     * @param string $tipo
     * @param string $value
    */
    private function setCerteza(string $tipo, string $value)
    {
        if (!$this->certeza[$tipo]) {
            $pregTipo = "s|seg|segundo";
            $param = ["s", "s"];

            switch ($tipo) {
                case 'h':
                    $pregTipo = "h|hour|hora|hs|hr";
                    $param = ["i", "s"];
                    break;

                case 'i':
                    $pregTipo = "m|min|minuto";
                    break;
            }

            if (isset($this->data[$tipo]) && $tipo !== "s") {
                $this->setTimeInfo($this->data[$tipo], $param);
            }

            $preg = "/\s*(\d{1,2})\s*(" . $pregTipo . ")\s*/i";
            preg_match($preg, $value, $matches);
            if ($tipo !== 'h' || $matches[1] < 24) {
                $this->data[$tipo] = (int)$matches[1];
                $this->certeza[$tipo] = true;
            } else {
                $this->setErro("hora acima do padrão 24 horas. Valor recebido {$matches[1]}.");
            }
        }
    }

    /**
     * Troca informações de tempo entre os paramentros por receber um valor mais preciso.
     * @param int $value
     * @param array $param
     */
    private function setTimeInfo(int $value, array $param)
    {
        if (!$this->certeza[$param[0]] && !isset($this->data[$param[0]])) {
            $this->data[$param[0]] = $value;

        } elseif (!$this->certeza[$param[1]] && !isset($this->data[$param[1]])) {
            $temp = $this->data[$param[0]];
            $this->data[$param[0]] = $value;
            $this->data[$param[1]] = $temp;

        } elseif ($this->certeza[$param[0]]) {
            if (!$this->certeza[$param[1]]) {
                $this->data[$param[1]] = $value;
            }

        } elseif ($this->certeza[$param[1]]) {
            if (!$this->certeza[$param[0]]) {
                $this->data[$param[0]] = $value;
            }
        }
    }
}