<?php

namespace Helpers;

class DateTime
{
    private $data;
    private $certeza = array("dia" => false, "mes" => false, "ano" => false, "h" => false, "i" => false, "s" => false);
    private $position;
    private $meses;
    private $erro;

    public function __construct()
    {
        $this->meses[0] = ["janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"];
        $this->meses[1] = ["january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december"];
        $this->meses[2] = $this->reduzDateFormat($this->meses[0]);
        $this->meses[3] = $this->reduzDateFormat($this->meses[1]);
    }

    private function setAno($ano, $certeza = false) {
        if(!$this->certeza['ano'] && !empty($ano) && is_numeric($ano) && (strlen($ano) === 2 || strlen($ano) === 4)) {
            $ano = (int)(strlen($ano) === 2 ? 2000 : 0 ) + $ano;
            if($ano > 0 && $ano < 2120) {
                $this->data['ano'] = (int)$ano;
                if($certeza) {
                    $this->certeza['ano'] = true;
                }
            }
        }
    }

    private function setMes($mes, $certeza = false) {
        if(!$this->certeza['mes'] && !empty($mes)) {
            if (is_string($mes))
                $mes = $this->getMonthNumber($mes);

            if (is_numeric($mes) && $mes > 0 && $mes < 13) {
                $this->data['mes'] = (int)$mes;
                if($certeza)
                    $this->certeza['mes'] = true;
            }
        }
    }

    private function setDia($dia, $certeza = false) {
        if(!$this->certeza['dia'] && !empty($dia) && is_numeric($dia) && $dia > 0 && $dia < 32) {
            $this->data['dia'] = (int)$dia;
            if($certeza) {
                $this->certeza['dia'] = true;
            }
        }
    }

    private function setHora($hora, $certeza = false) {
        if(!$this->certeza['h'] && !empty($hora) && is_numeric($hora) && $hora > -1 && $hora < 24) {
            $this->data['h'] = (int) $hora;
            if($certeza) {
                $this->certeza['h'] = true;
            }
        }
    }

    private function setMinuto($min, $certeza = false) {
        if(!$this->certeza['i'] && !empty($min) && is_numeric($min) && $min > -1 && $min < 60) {
            $this->data['i'] = (int) $min;
            if($certeza) {
                $this->certeza['i'] = true;
            }
        }
    }

    private function setSegundo($seg, $certeza = false) {
        if(!$this->certeza['s'] && !empty($seg) && is_numeric($seg) && $seg > -1 && $seg < 60) {
            $this->data['s'] = (int) $seg;
            if($certeza) {
                $this->certeza['s'] = true;
            }
        }
    }

    /**
     * Recebe um date em formato aleatório e retorna um date no padrão informado ou que esta por padrão
     * @param string $date
     * @param string $pattern
     * @return mixed
     */
    public function getDateTime(string $date, string $pattern = "Y-m-d H:i:s")
    {
        if (!$date)
            $this->data = date($pattern);
        else
            $this->prepareDateTime($date);

        if($this->erro || !isset($this->data['ano']) || !isset($this->data['mes']) || !isset($this->data['dia'])) {
            return null;
        } else {
            $strtoTime = "";
            if (isset($this->data['h']) && $this->data['h'] > 11) {
                $this->data['h'] -= 12;
                $strtoTime = "+12hours";
            }

            $originalDate = $this->data['ano'] . '-' . $this->data['mes'] . '-' . $this->data['dia'] . " " . ($this->data['h'] ?? 0) . ":" . ($this->data['i'] ?? 0) . ":" . ($this->data['s'] ?? 0);
            return date($pattern, strtotime($originalDate . $strtoTime));
        }
    }

    /**
     * @return string
     */
    public function getErro(): string
    {
        return $this->erro;
    }

    /**
     * Procura por expressões que coincidem com os modelos esperados
     * @param string $dado
     */
    private function prepareDateTime(string $dado)
    {
        $dado = " ".strip_tags(trim($dado))." ";

        if(preg_match("/\D(\d{1,2})*\W*(jan|fev|feb|mar|abr|apr|mai|may|jun|jul|ago|aug|set|sep|out|oct|nov|dez|dec)(eiro|uary|ereiro|bruary|ço|ch|ho|e|y|sto|ust|embro|termber|ubro|ober|ember)*\D/i", $dado, $diaMes)){
            $this->setMes($diaMes[2], true);
            if(!empty($diaMes[1]))
                $this->setDia($diaMes[1]);
        }

        if(preg_match("/\D((20|19)\d{2})\D/i", $dado, $ano))
            $this->setAno($ano[1], true);

        if(preg_match('/ (\d{1,2}),/i', $dado, $dia))
            $this->setDia($dia[1], true);

        if(preg_match("/\D(\d{1,4})[-\/,._;\\\](\d{1,2})[-\/,._;\\\](\d{1,4})\D/i", $dado, $dateTime)){

            if(!(strlen($dateTime[3]) > 2 && strlen($dateTime[1]) > 2)) {
                $ano = date('y');
                if (strlen($dateTime[3]) < 3 && (($dateTime[1] > 31 && $dateTime[3] < 32 && $dateTime[3] > 0) || (strlen($dateTime[3]) === 1 && strlen($dateTime[1]) > 1) ||
                        (strlen($dateTime[3]) === 2 && strlen($dateTime[1]) === 2 &&
                            ($dateTime[1] === $ano && $dateTime[3] !== $ano)
                            || ($dateTime[1] > ($ano - 3) && $dateTime[1] < ($ano + 2) && ($dateTime[3] > ($ano + 1) || $dateTime[3] < ($ano - 2)))))) {

                    $this->setAno($dateTime[1], true);
                    $this->setMes($dateTime[2], true);
                    $this->setDia($dateTime[3], true);
                } else {
                    $this->setAno($dateTime[3], $dateTime[3] > 31 && $dateTime[1] < 32);
                    $this->setMes($dateTime[2], true);
                    $this->setDia($dateTime[1], $dateTime[3] > 31 && $dateTime[1] < 32);
                }
            }
        }

        if(preg_match("/\D(\d{1,2})\W*h(r|s|our|ora|ours|oras)*[^a-zA-Z]/i", $dado, $hora)){
            $this->setHora($hora[1], true);
        }

        if(preg_match("/\D(\d{1,2})\W*m(i|in|inuto|inutos|inute|inutes)*[^a-zA-Z]/i", $dado, $minuto)){
            $this->setMinuto($minuto[1], true);
        }

        if(preg_match("/\D(\d{1,2})\W*s(e|eg|egundo|egundos|econd|econds)*[^a-zA-Z]/i", $dado, $segundo)){
            $this->setSegundo($segundo[1]);
        }

        if(preg_match("/\D(\d{1,2}):(\d{1,2})(:(\d{1,2}))*\D/i", $dado, $tempo)){
            $this->setHora($tempo[1], isset($tempo[4]));
            $this->setMinuto($tempo[2], isset($tempo[4]));
            if(isset($tempo[4])) {
                $this->setSegundo($tempo[4], true);
            }
        }
    }

    /**
     * Verifica qual parametro (dia, mes, ano) é mais preferível receber o valor encontrado,
     * $param1 > $param2 > $param3
     * @param string $param1
     * @param string $param2
     * @param mixed $param3
     * @return mixed
     */
    private function setDateInfo(string $param1, string $param2, $param3 = null)
    {
        if (!$this->certeza[$param1] && !isset($this->data[$param1])) {
            return $param1;

        } elseif (!$this->certeza[$param2] && !isset($this->data[$param2])) {
            return $param2;

        } elseif (isset($param3) && !$this->certeza[$param3] && !isset($this->data[$param3])) {
            return $param3;

        } elseif (!$this->certeza[$param1]) {
            return $param1;

        } elseif (!$this->certeza[$param2]) {
            return $param2;

        } elseif (isset($param3) && !$this->certeza[$param3]) {
            return $param3;

        }

        return null;
    }

    /**
     * Ajusta posição do mês, caso este tenha sido encontrado na ponta da data passada,
     * verifica a possibilidade de trocar a informação do mês com uma das pontas (dia ou ano)
     * se for possível, ele troca a informação, mantendo o campo mês, no meio da data passada.
     */
    private function checkMonthEdge()
    {
        if(!$this->erro) {
            asort($this->position);
            $this->position = array_keys($this->position);

            if ((isset($this->position[0]) && $this->position[0] === "mes") || (isset($this->position[2]) && $this->position[2] === "mes")) {
                $n = $this->position[1];
                if (!$this->certeza['mes'] && !$this->certeza[$n] && $this->data[$n] < 13 && $this->data[$n] > 0) {
                    $temp = $this->data['mes'];
                    $this->data['mes'] = $this->data[$n];
                    $this->data[$n] = $temp;
                }
            }
        }
    }

    /**
     * Passa um mês por escrito e retorna seu número equivalente
     * @param mixed $month
     * @return int
     */
    private function getMonthNumber($month) :int
    {
        $month = strtolower($month);
        if(is_numeric($month))
            return $month;

        foreach ($this->meses as $mes) {
            if (in_array($month, $mes))
                return (array_search($month, $mes) + 1);
        }

        return date("m");
    }

    /**
     * Reduz as strings de uma lista em strings de até 3 caracteres
     * @param array $date
     * @return array
     */
    private function reduzDateFormat(array $date): array
    {
        $data = [];
        foreach ($date as $item) {
            $data[] = substr($item, 0, 3);
        }
        return $data;
    }

    private function checkErro()
    {
        if(!isset($this->data['dia']) || empty($this->data['dia'])){
            $this->erro = "não foi possível encontrar o dia de uma data válida.";

        } elseif(!isset($this->data['mes']) || empty($this->data['mes'])){
            $this->erro = "não foi possível encontrar o mês de uma data válida.";

        } elseif(!isset($this->data['ano']) || empty($this->data['ano'])) {
            $this->erro = "não foi possível encontrar o ano de uma data válida.";

        }
    }
}