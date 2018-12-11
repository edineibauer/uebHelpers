<?php

namespace Helpers;

class Date
{
    private $data;
    private $certeza = array("dia" => false, "mes" => false, "ano" => false);
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

    /**
     * Recebe um date em formato aleatório e retorna um date no padrão informado ou que esta por padrão
     * @param mixed $date
     * @param string $pattern
     * @return mixed
     */
    public function getDate($date, string $pattern = "Y-m-d")
    {
        if (!$date) {
            $this->data['ano'] = date("Y");
            $this->data['mes'] = date("m");
            $this->data['dia'] = date("d");
        } else {
            $this->prepareDate($date);
        }

        if($this->erro) {
            return null;
        } else {
            $originalDate = $this->data['ano'] . '-' . $this->data['mes'] . '-' . $this->data['dia'];
            return date($pattern, strtotime($originalDate));
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
     * Particiona a data recebida para analisar qual informação diz respeito ao dia, mês e ano
     * @param string $date
     */
    private function prepareDate(string $date)
    {
        if(preg_match("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/i", $date)) $date = explode(" ", $date)[0];

        foreach (preg_split("/\W/i", $date) as $i => $dado) {
            $this->getDatePart($i, $dado);
        }

        $this->checkErro();
        $this->checkMonthEdge();
    }

    /**
     * Verifica dados básicos da informação, e manda para verificações mais precisas
     * @param int $i
     * @param string $dado
     */
    private function getDatePart(int $i, string $dado)
    {
        $dado = strip_tags(trim($dado));
        if (!empty($dado)) {
            if (!is_numeric($dado)) {
                $dado = (string)$dado;
                $this->checkNameMonth($dado, $i);

            } else {
                $dado = (int)$dado;

                if ($dado > 31) {
                    $this->setAno($dado, $i);

                } elseif ($dado > 0) {
                    $this->checkWhichPart($dado, $i);
                }
            }
        }
    }

    /**
     * Verifica Se a string encontrada diz respeito a um mês do calendário
     * @param int $i
     * @param string $dado
     */
    private function checkNameMonth(string $dado, int $i)
    {
        foreach ($this->meses as $mes) {
            if (in_array($dado, $mes)) {
                $this->setMes(array_search($dado, $mes) + 1, $i);
                break;
            }
        }
    }

    /**
     * filtra para determinar onde aplicar este valor inteiro.
     * @param int $position
     * @param int $dado
     */
    private function checkWhichPart(int $dado, int $position)
    {
        if ($dado > 12) {
            $current = $this->setDateInfo('dia', 'ano');
            if ($current) {
                $this->data[$current] = $dado;
                $this->position[$current] = $position;
            }
        } else {
            $current = $this->setDateInfo('mes', 'dia', 'ano');
            if ($current) {
                $this->data[$current] = $dado;
                $this->position[$current] = $position;
            }
        }
    }

    /**
     * seta a informação de mês e verifica se já possui um dado anterior,
     * se tiver, verifica a possibilidade de passar para outro setor.
     * @param int $position
     * @param int $mes
     */
    private function setMes(int $mes, int $position)
    {
        if (!$this->certeza['mes']) {
            if (isset($this->data['mes'])) {
                $current = $this->setDateInfo('dia', 'ano');
                if ($current) {
                    $this->data[$current] = $this->data['mes'];
                    $this->position[$current] = $this->position['mes'];
                }
            }
            $this->data['mes'] = $mes;
            $this->certeza['mes'] = true;
            $this->position['mes'] = $position;
        }
    }

    /**
     * seta a informação de ano e verifica se já possui um dado anterior,
     * se tiver, verifica a possibilidade de passar para outro setor.
     * @param int $position
     * @param int $ano
     */
    private function setAno($ano, $position)
    {
        if (!$this->certeza['ano']) {
            $this->certeza['ano'] = true;

            if (isset($this->data['ano']) && $this->data['ano'] < 32) {
                $this->checkWhichPart($this->data['ano'], $this->position['ano']);
            }

            $this->data['ano'] = $ano;
            $this->position['ano'] = $position;
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

    /**
     * Verifica por erros
    */
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