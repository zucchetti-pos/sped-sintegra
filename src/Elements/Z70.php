<?php

/**
 * This file belongs to the NFePHP project
 * php version 7.0 or higher
 *
 * @category  Library
 * @package   NFePHP\Sintegra
 * @copyright 2019 NFePHP Copyright (c)
 * @license   https://opensource.org/licenses/MIT MIT
 * @author    Roberto L. Machado <linux.rlm@gmail.com>
 * @link      http://github.com/nfephp-org/sped-sintegra
 */

namespace NFePHP\Sintegra\Elements;

/**
 * Nota fiscal de serviço de transporte
 */

use NFePHP\Sintegra\Common\Element;
use NFePHP\Sintegra\Common\ElementInterface;

class Z70 extends Element implements ElementInterface
{
    const REGISTRO = '70';

    protected $parameters = [
        'CNPJ' => [
            'type' => 'numeric',
            'regex' => '^[0-9]{11,14}$',
            'required' => true,
            'info' => 'CNPJ do emitente nas entradas e dos destinátarios nas saídas',
            'format' => 'totalNumber',
            'length' => 14
        ],
        'IE' => [
            'type' => 'string',
            'regex' => '^ISENTO|[0-9]{2,14}$',
            'required' => false,
            'info' => 'Inscrição estadual do emitente nas entradas e do destinatário nas saídas',
            'format' => '',
            'length' => 14
        ],
        'DATA_EMISSAO' => [
            'type' => 'string',
            'regex' => '^(2[0-9]{3})(0?[1-9]|1[012])(0?[1-9]|[12][0-9]|3[01])$',
            'required' => true,
            'info' => 'Data de emissão na saída ou de recebimento na entrada',
            'format' => '',
            'length' => 8
        ],
        'UF' => [
            'type' => 'string',
            'regex' => '^(AC|AL|AM|AP|BA|CE|DF|ES|GO|MA|MG|MS|MT|PA|PB|PE|PI|PR|RJ|RN|RO|RR|RS|SC|SE|SP|TO|EX)$',
            'required' => true,
            'info' => 'Sigla da Unidade da Federação do emitente',
            'format' => 'empty',
            'length' => 2
        ],
        'COD_MOD' => [
            'type' => 'numeric',
            'regex' => '^[0-9]{2}$',
            'required' => true,
            'info' => 'Código do modelo da nota fiscal',
            'format' => 'totalNumber',
            'length' => 2
        ],
        'SERIE' => [
            'type' => 'string',
            'regex' => '^[0-9]{1}$',
            'required' => true,
            'info' => 'Série do documento fiscal',
            'format' => '',
            'length' => 1
        ],
        'SUB_SERIE' => [
            'type' => 'string',
            'regex' => '^.{1,2}$',
            'required' => false,
            'info' => 'Série do documento fiscal',
            'format' => 'empty',
            'length' => 2
        ],
        'NUM_DOC' => [
            'type' => 'numeric',
            'regex' => '^[0-9]{1,6}$',
            'required' => true,
            'info' => 'Número do documento fiscal',
            'format' => 'totalNumber',
            'length' => 6
        ],
        'CFOP' => [
            'type' => 'numeric',
            'regex' => "^[1,2,3,5,6,7]{1}[0-9]{3}$",
            'required' => true,
            'info' => 'Código Fiscal de Operação e Prestação',
            'format' => '',
            'length' => 4
        ],
        'VL_TOTAL' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info' => 'Valor total da nota fiscal (com 2 decimais)',
            'format' => 'totalNumber',
            'length' => 13
        ],
        'VL_BC_ICMS' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info' => 'Base de Cálculo do ICMS (com 2 decimais)',
            'format' => 'totalNumber',
            'length' => 14
        ],
        'VL_ICMS' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info' => 'Montante do imposto (com 2 decimais)',
            'format' => 'totalNumber',
            'length' => 14
        ],
        'ISENTA_NTRIBUTADA' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info' => 'Valor amparado por isenção ou não incidência (com 2 decimais)',
            'format' => 'totalNumber',
            'length' => 14
        ],
        'OUTRAS' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info' => 'Valor que não confira débito ou crédito do ICMS (com 2 decimais)',
            'format' => 'totalNumber',
            'length' => 14
        ],
        'MOD_FRETE' => [
            'type' => 'string',
            'regex' => '^(0|1|2)$',
            'required' => true,
            'info' => 'Modalidade do frete (1 – CIF; 2 – FOB; 0 - OUTROS)',
            'format' => '',
            'length' => 1
        ],
        'SITUACAO' => [
            'type' => 'string',
            'regex' => '^(S|N|E|X|2|4)$',
            'required' => true,
            'info' => 'Situação do documento fiscal (N - Documento Fiscal Normal;'
            . ' S - Documento Fiscal Cancelado; E - Lançamento Extemporâneo de '
            . 'Documento Fiscal Normal; X - Lançamento Extemporâneo de Documento '
            . 'Fiscal Cancelado; 2 - Documento com USO DENEGADO; 4 - Documento '
            . 'com USO inutilizado)',
            'format' => '',
            'length' => 1
        ],
    ];

    /**
     * Constructor
     * @param \stdClass $std
     */
    public function __construct(\stdClass $std)
    {
        parent::__construct(self::REGISTRO);
        $this->std = $this->standarize($std);
        $this->postValidation();
    }
}
