<?php

use PHPUnit\Framework\TestCase;

final class csv_converterTest extends TestCase
{


    public function testMustReturnSuccess0()
    {
        exec('php csv_converter -i "Test/testCSV1.csv" -c Test/conf1.php -o Test/output.csv', $output, $lastStr);
        $this->assertEquals('0', $lastStr);
        exec('php csv_converter --input "Test/testCSV1.csv" --config Test/conf1.php --output Test/output.csv', $output,
            $lastStr);
        $this->assertEquals('0', $lastStr);
        exec('php csv_converter -h', $output, $lastStr);
        $this->assertEquals('0', $lastStr);
        exec('php csv_converter --help', $output, $lastStr);
        $this->assertEquals('0', $lastStr);
    }

    public function testMustReturnFailsWithWrongParameters()
    {
        exec('php csv_converter  -c Test/conf1.php -o Test/output.csv', $output, $lastStr);
        $this->assertNotEquals('0', $lastStr);
        exec('php csv_converter --input "Test/testCSV1.csv" Test/conf1.php --output Test/output.csv', $output,
            $lastStr);
        $this->assertNotEquals('0', $lastStr);
        exec('php csv_converter -h -i "Test/testCSV1.csv"', $output, $lastStr);
        $this->assertNotEquals('0', $lastStr);
        exec('php csv_converter -h --help', $output, $lastStr);
        $this->assertNotEquals('0', $lastStr);
    }

    public function testMustWork()
    {
        function isThisTwoCSVFilesEquals($firstFilename, $SecondFilename)
        {
            $isEqual = true;
            if (is_file($firstFilename) && is_file($SecondFilename)) {
                if ((($handle = fopen($firstFilename, "r")) !== false) &&
                    (($handle2 = fopen($SecondFilename, "r")) !== false)) {
                    while ((($data = fgetcsv($handle, 0)) !== false) &&
                        (($data2 = fgetcsv($handle2, 0)) !== false)) {
                        if (!empty(array_diff($data, $data2))) {
                            $isEqual = false;
                        }
                    }
                    fclose($handle);
                    fclose($handle2);
                }
            }
            if ($isEqual) {
                return true;
            } else {
                return false;
            }
        }

        exec('php csv_converter -i "Test/testCSV1.csv" -c Test/notChangedConf.php -o Test/output.csv', $output,
            $lastStr);
        $this->assertTrue(isThisTwoCSVFilesEquals("Test/testCSV1.csv", "Test/output.csv"));
    }

    public function testMustReturnFailsWithWrongFileType()
    {
        exec('php csv_converter -i "Test/testCSV1.csv" -c "Test/testCSV1.csv" -o Test/output.csv', $output, $lastStr);
        $this->assertNotEquals('0', $lastStr);
    }

    public function testMustReturnFailsWithNotReadableFile()
    {
        exec('php csv_converter -i "Test/notReadableCSV.csv" -c Test/notChangedConf.php -o Test/output.csv', $output,
            $lastStr);
        $this->assertNotEquals('0', $lastStr);
        exec('php csv_converter -i "Test/testCSV1.csv" -c Test/notChangedConf.php -o "Test/notReadableCSV.csv"',
            $output, $lastStr);
        $this->assertNotEquals('0', $lastStr);
    }

    public function testMustReturnFileInSomeEncodingAsInputFileCURRENTNOTWORK()
    {
        $value1=file_get_contents("Test/testCSV1.csv");
        $encoding1 = mb_detect_encoding($value1);
        exec('php csv_converter -i "Test/notReadableCSV.csv" -c Test/notChangedConf.php -o Test/output.csv', $output,$lastStr);
        $value2=file_get_contents("Test/output.csv");
        $encoding2 = mb_detect_encoding($value2);
        $this->assertEquals($encoding1, $encoding2);

//        $value1=file_get_contents("Test/Windows-1251.csv");
//        $encoding1 = mb_detect_encoding($value1, array('UTF-8', 'Windows-1251'));
//        exec('php csv_converter -i "Test/Windows-1251.csv" -c Test/notChangedConf.php -o Test/output.csv', $output,$lastStr);
//        $value2=file_get_contents("Test/output.csv");
//        $encoding2 = mb_detect_encoding($value2);
//        $this->assertEquals($encoding1, $encoding2);
    }
}





