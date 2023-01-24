<?php

namespace NFePHP\Sintegra\Common;

use \stdClass;
use NFePHP\Common\Strings;
use NFePHP\Sintegra\Common\ElementInterface;
use Exception;

abstract class Element implements ElementInterface
{

    public $std;
    public $values;
    protected $parameters;
    private $reg;

    /**
     * Constructor
     * @param string $reg
     */
    public function __construct($reg)
    {
        $this->reg = $reg;
        $this->values = new stdClass();
    }

    public function postValidation()
    {
        return true;
    }

    /**
     * Valida e ajusta os dados de entrada para os padões estabelecidos
     * @param \stdClass $std
     */
    protected function standarize(\stdClass $std)
    {
        if (empty($this->parameters)) {
            throw new Exception('Parametros não estabelecidos na classe');
        }
        $errors = [];
        //passa todos as variáveis do stdClass para minusculo
        $arr = array_change_key_case(get_object_vars($std), CASE_LOWER);
        $std = json_decode(json_encode($arr));
        //paga as chaves dos parametros e passa para minusculo
        $stdParam = json_decode(json_encode($this->parameters));
        $this->parameters = array_change_key_case(get_object_vars($stdParam), CASE_LOWER);
        $paramKeys = array_keys($this->parameters);
        //passa os paramatros com as chaves modificadas para um stdClass
        if (!$json = json_encode($this->parameters)) {
            throw new \RuntimeException("Falta definir os parametros ou existe erro no array");
        }
        $stdParam = json_decode($json);
        if ($stdParam === null) {
            throw new \RuntimeException("Houve uma falha na converção para stdClass");
        }
        //verifica se foram passados os dados obrigatórios
        foreach ($std as $key => $value) {
            if (!isset($stdParam->$key)) {
                //ignore non defined params
                continue;
            }
            if ($stdParam->$key->required && $std->$key === null) {
                $errors[] = "$key é requerido.";
            }
        }
        $newstd = new \stdClass();
        foreach ($paramKeys as $key) {
            if (!key_exists($key, $arr)) {
                $newstd->$key = null;
            } else {
                if ($std->$key === null) {
                    $newstd->$key = null;
                    continue;
                }
                //se o valor para o parametro foi passado, então validar
                $resp = $this->isFieldInError(
                    $std->$key,
                    $stdParam->$key,
                    strtoupper($key),
                    $this->reg,
                    $stdParam->$key->required
                );
                if ($resp) {
                    $errors[] = $resp;
                }
                //e formatar o dado passado
                $formated = $this->formater(
                    $std->$key,
                    $stdParam->$key->format,
                    strtoupper($key),
                    $stdParam->$key->length
                );

                $newValue = $this->formatString(
                    $formated,
                    $stdParam->$key,
                    $stdParam->$key->type
                );

                $newstd->$key = $newValue;
            }
        }
        //se algum erro for detectado disparar um Exception
        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode("\n", $errors));
        }
        return $newstd;
    }

    /**
     * Verifica os campos comrelação ao tipo e seu regex
     * @param string|integer|float|null $input
     * @param stdClass $param
     * @param string $fieldname
     * @return string|boolean
     */
    protected function isFieldInError($input, $param, $fieldname, $element, $required)
    {
        $type = $param->type;
        $regex = $param->regex;
        if (empty($regex)) {
            return false;
        }
        if (($input === null || $input === '') && !$required) {
            return false;
        }
        switch ($type) {
            case 'integer':
                if (!is_numeric($input)) {
                    return "[$this->reg] $element campo: $fieldname deve ser um valor numérico inteiro.";
                }
                break;
            case 'numeric':
                if (!is_numeric($input)) {
                    return "[$this->reg] $element campo: $fieldname deve ser um numero.";
                }
                break;
            case 'string':
                if (!is_string($input)) {
                    return "[$this->reg] $element campo: $fieldname deve ser uma string.";
                }
                break;
        }
        $input = (string) $input;
        if ($regex === 'email') {
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return "[$this->reg] $element campo: $fieldname Esse email [$input] está incorreto.";
            }
            return false;
        }
        if (!preg_match("/$regex/", $input)) {
            return "[$this->reg] $element campo: $fieldname valor incorreto [$input]. (validação: $regex)";
        }
        return false;
    }

    /**
     * Formata os campos float
     * @param string|integer|float|null $value
     * @param string $format
     * @param string $fieldname
     * @return int|string|float|null
     * @throws \InvalidArgumentException
     */
    protected function formater($value, $format = null, $fieldname = '', $length)
    {
        if ($value === null) {
            return $value;
        }
        if (!is_numeric($value)) {
            //se não é numerico então passa para ASCII
            $value = Strings::toASCII($value);
        }
        if (empty($format)) {
            return $value;
        }
        //gravar os valores numericos para possivel posterior validação complexa
        $name = strtolower($fieldname);
        if ($value === '' && $format !== 'empty') {
            $value = 0;
        }

        if ($format == 'totalNumber') {
            return $this->numberTotalFormat(floatval($value), $length);
        }

        if ($format == 'aliquota') {
            return $this->numberFormatAliquota($value, $length);
        }

        if ($format == 'empty') {
            return $this->formatFieldEmpty($value, $length);
        }


        $this->values->$name = (float) $value;
        return $this->numberFormat(floatval($value), $format, $fieldname);
    }

    /**
     * Format number
     * @param float $value
     * @param string $format
     * @return string
     * @throws \InvalidArgumentException
     */
    private function numberFormat($value, $format, $fieldname)
    {
        $n = explode('v', $format);
        $mdec = strpos($n[1], '-');
        $p = explode('.', "{$value}");
        $ndec = !empty($p[1]) ? strlen($p[1]) : 0; //decimal digits
        $nint = strlen($p[0]); //integer digits
        $intdig = (int) $n[0];
        if ($nint > $intdig) {
            throw new \InvalidArgumentException("[$this->reg] O [$fieldname] é maior "
            . "que o permitido [$format].");
        }
        if ($mdec !== false) {
            //is multi decimal
            $mm = explode('-', $n[1]);
            $decmin = (int) $mm[0];
            $decmax = (int) $mm[1];
            //verificar a quantidade de decimais informada
            //se maior ou igual ao minimo e menor ou igual ao maximo
            if ($ndec >= $decmin && $ndec <= $decmax) {
                //deixa como está
                return number_format($value, $ndec, ',', '');
            }
            //se menor que o minimo, formata para o minimo
            if ($ndec < $decmin) {
                return number_format($value, $decmin, ',', '');
            }
            //se maior que o maximo, formata para o maximo
            if ($ndec > $decmax) {
                return number_format($value, $decmax, ',', '');
            }
        }
        $decplaces = (int) $n[1];
        return number_format($value, $decplaces, ',', '');
    }


    private function numberTotalFormat($value, $length)
    {
        return str_pad($value, $length, "0", STR_PAD_LEFT);
    }

    private function numberFormatAliquota($value, $length)
    {
        return str_pad($value, $length, "0", STR_PAD_LEFT);
    }

    private function formatFieldEmpty($value, $length)
    {
        return str_pad($value, $length, " ", STR_PAD_RIGHT);
    }

    /**
     * Retorna string conforme o tamanho, pois sintegra considera as posições
     * @return string
     */
    protected function formatString($value, $param, $type)
    {
        $pad = STR_PAD_LEFT;
        $strchar = '0';
        if ($type == 'string') {
            $pad = STR_PAD_RIGHT;
            $strchar = ' ';
        }
        $length = $param->length;
        $newValue = strtoupper(str_pad($value, $length, $strchar, $pad));
        return $newValue;
    }

    /**
     * Construtor do elemento
     * @return string
     */
    protected function build()
    {
        $register = '';
        foreach ($this->parameters as $key => $params) {
            $register .= $this->std->$key;
        }
        return $register;
    }

    /**
     * Retorna o elemento formatado em uma string
     * @return string
     */
    public function __toString()
    {
        return  $this->reg . $this->build();
    }
}
