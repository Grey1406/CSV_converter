<?php

use PHPUnit\Framework\TestCase;

final class csv_converterTest extends TestCase
{
    public function testMustRun(): void
    {
        exec('php csv_converter -i "example.csv" -c confExample.php -o output.csv',$output,$lastStr);
        $this->assertEquals('0',$lastStr);
    }
}
