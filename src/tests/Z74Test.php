<?php

namespace NFePHP\Sintegra\Tests;

use NFePHP\Sintegra\Elements\Z74;
use PHPUnit\Framework\TestCase;
use stdClass;

class Z74Test extends TestCase
{
    public function testZ74()
    {
        $std = new stdClass();
        $std->DATA_INVENTARIO = '20201231';
        $std->CODIGO_PRODUTO = '000016';
        $std->QUANTIDADE = '0000000009';
        $std->VL_PRODUTO = '0000000000006';
        $std->CODIGO_POSSE = '2';
        $std->UF = 'SC';
        $b1 = new Z74($std);
        $resp = "{$b1}";

        $expected = '7420201231000016        00000000090000000000006253100000000000000              SC';
        $this->assertEquals($expected, $resp);
    }
}
