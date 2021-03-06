<?php

function detectEOL($originalFilename)
{
    if (($fo = fopen($originalFilename, "r")) !== false) {
        $row = fgets($fo);
        $eol = substr($row, -2);
        if ($eol == "\r\n") {
            return "\r\n";
        } elseif ($eol == "\n\r") {
            return "\n\r";
        } elseif (substr($eol, -1) == "\n") {
            return "\n";
        } elseif (substr($eol, -1) == "\r") {
            return "\r";
        }
    }
    return false;
}

//функция разбора переданных аргументов и проверки файлов
function argParse($options, $argc)
{

//    $countParam=1;
//    foreach ($options as $k=>$v){
//        $countParam+=($k=='h'|$k=='help'|$k=='skip-first'|$k=='strict')?1:2;
//    }
//    if($countParam!=$argc){
//        getError(['присутсвует неверный параметр'."\n"]);
//        exit(1);
//    }
    if (isset($options['h']) || isset($options['help'])) {
        getError([
            'Ошибка параметра, произошли следующие ошибки:',
            'вызов справки совместно с выполнением кода, выберите что-то одно'
        ]);
    }
    //установка флага skip-first
    $skipFirst = isset($options['skip-first']);
    //установка разделителя
    $delimiter = ',';
    if (isset($options['delimiter'])) {
        if (isset($options['d'])) {
            getError(['Ошибка параметра, произошли следующие ошибки:', 'попытка задать делитель передав две опции']);
        } else {
            $delimiter = $options['delimiter'];
        }
    } else {
        if (isset($options['d'])) {
            $delimiter = $options['d'];
        }
    }
    //установка флага strict
    $strict = isset($options['strict']);
    //установка имени входного файла и проверки
    $originalFilename = '';
    if (isset($options['input'])) {
        if (isset($options['i'])) {
            getError([
                'Ошибка параметра, произошли следующие ошибки:',
                'попытка задать входной файл передав две опции'
            ]);
        } else {
            $originalFilename = $options['input'];
        }
    } else {
        if (isset($options['i'])) {
            $originalFilename = $options['i'];
        }
    }
    if (!is_readable($originalFilename) || $originalFilename == '') {
        getError(['Ошибка входного файла', 'файл не существует или не доступен для чтения']);
    }
    //установка имени файла конфигурации и проверки
    $confFilename = '';
    if (isset($options['config'])) {
        if (isset($options['с'])) {
            getError([
                'Ошибка параметра, произошли следующие ошибки:',
                'попытка задать файл конфигурации передав две опции'
            ]);
        } else {
            $confFilename = $options['config'];
        }
    } else {
        if (isset($options['c'])) {
            $confFilename = $options['c'];
        }
    }
    if (!is_file($confFilename) || $confFilename == '') {
        getError(['Ошибка файла конфигурации', 'передан не файл или файл не существует']);
    }
    if (!is_readable($confFilename)) {
        getError(['Ошибка файла конфигурации', 'файл не существует или не доступен для чтения']);
    }
    //установка имени выходного файла и проверки
    $newFilename = '';
    if (isset($options['output'])) {
        if (isset($options['o'])) {
            getError([
                'Ошибка параметра, произошли следующие ошибки:',
                'попытка задать выходной файл передав две опции'
            ]);
        } else {
            $newFilename = $options['output'];
        }
    } else {
        if (isset($options['o'])) {
            $newFilename = $options['o'];
        }
    }
    if ($newFilename == '') {
        getError(['Ошибка выходного файла', 'передан не файл или файл не существует']);
    }
    //возврат установленных значений
    return [$skipFirst, $delimiter, $strict, $originalFilename, $confFilename, $newFilename];
}

//функция проверки соответствия числа столбцов исходного файла и файла конфигурации
function testStrict($data, $funcArray)
{
    $keys = array_keys($funcArray);
    $max = max($keys);
    if (count($data) < $max) {
        getError([
            'количество столбцов исходного файла не соответсвует требуемому для конвертаци'
            ,
            'проверьте исходный файл и файл конфигурации'
        ]);
    }
}

//функция сохранения файла
function saveNewFile($newFilename, $newArray, $delimiter, $EOL, $encoding)
{
    if (is_writable($newFilename)) {
        file_put_contents($newFilename, '');
        if (($handle = fopen($newFilename, 'w+')) !== false) {
            foreach ($newArray as $key => $row) {
                $newRow = [];
                foreach ($row as $item) {
                    if (strstr($item, $delimiter) || strstr($item, "\"")) {
                        $item = str_replace("\"", "\"\"", $item);
                        $item = "\"" . $item . "\"";
                    }
                    $newRow[] = $item;
                }
                if ($key < count($newArray) - 1) {
                    $str = implode($delimiter, $newRow) . "$EOL";
                } else {
                    $str = implode($delimiter, $newRow);
                }
                if ($encoding != 'UTF-8') {
                    $str = mb_convert_encoding($str, 'Windows-1251');
                }
                if (fwrite($handle, $str) === false) {
                    getError(['Ошибка выходного файла, при записи в файл произошла непредвиденная ошибка']);
                }
            }
            fclose($handle);
        }
    } else {
        getError(['Ошибка выходного файла, произошли следующие ошибки:', 'файл не доступен для записи']);
    }
}

//функция загрузки файла конфигурации
function loadConfFile($confFilename)
{
    ob_start();
    $funcArray = include $confFilename;
    ob_end_clean();
    if (!is_array($funcArray)) {
        getError(['переданный файл не содержит массив с инструкциями']);
    }
    return $funcArray;
}

//функция создания нового массива из исходных данных
function createNewArray(array $oldFile, array $funcArray, $skipFirst)
{
    $newArray = [];
    $i = 0;
    if ($skipFirst) {
        $i = 1;
        $newArray[] = $oldFile[0];
    }
    for (; $i < count($oldFile); $i++) {
        $newRow = [];
        for ($j = 0; $j < count($oldFile[$i]); $j++) {
            $newRow[] = getNewValue($funcArray, $oldFile[$i][$j], $oldFile[$i], $i, $j);
        }
        $newArray[] = $newRow;
    }
    return $newArray;
}

//функция получения нового значения для выходного файла
function getNewValue($funcArray, $value, $rowData, $rowIndex, $columnIndex)
{
    if (!key_exists($columnIndex, $funcArray)) {
        $returnedValue = $value;
    } else {
        if (is_callable($funcArray[$columnIndex])) {
            $faker = Faker\Factory::create();
            $returnedValue = $funcArray[$columnIndex]($value, $rowData, $rowIndex, $faker);
        } else {
            try {
                $faker = Faker\Factory::create();
                $funcName = $funcArray[$columnIndex];
                $returnedValue = $faker->$funcName;
                if (isset($returnedValue)) {
                    $returnedValue = $faker->$funcName;
                }
            } catch (Exception $e) {
                $returnedValue = $funcArray[$columnIndex];
            }
        }
    }

    return mb_convert_encoding($returnedValue, 'UTF-8');
}

//функция загрузки исходного файла в массив
function loadOriginFile($originalFilename, $delimiter, $EOL)
{
    $arrayCSV = [];
    $encoding = "";
    if (($handle = fopen($originalFilename, "r")) !== false) {
        while (($data = fgets($handle)) !== false) {
            if ($encoding == "") {
                if (mb_check_encoding($data, 'UTF-8')) {
                    $encoding = 'UTF-8';
                } else {
                    $encoding = 'Windows-1251';
                }
            }
            $data = str_replace($EOL, "", $data);
            if ($encoding == 'Windows-1251') {
                $data = mb_convert_encoding($data, 'UTF-8', 'Windows-1251');
            }
            $arrayCSV[] = str_getcsv($data, $delimiter);
        }
        fclose($handle);
    }
    $countColumns = count($arrayCSV[0]);
    foreach ($arrayCSV as $key => $row) {
        if (count(($row)) != $countColumns) {
            getError([
                'Ошибка входного файла, произошли следующие ошибки:',
                'файл содержит разное число столбцов, и не может считаться CVS файлом (строка ' . ($key + 1) . ')'
            ]);
        }
    }
    return [$encoding, $arrayCSV];
}

//функция вывода справки
function getReference()
{
    $reference = "\n";
    $reference .= "Usage:\n";
    $reference .= "  csv_converter.php --help \n";
    $reference .= "  csv_converter.php -i input_file_path -c configuration_file_path -o output_file_path\n";
    $reference .= "  csv_converter.php -i input_file_path -c configuration_file_path -o output_file_path [-d \",\"]\n";
    $reference .= "  csv_converter.php -i input_file_path -c configuration_file_path ";
    $reference .= "-o output_file_path [--skip-first]\n";
    $reference .= "  csv_converter.php -i input_file_path -c configuration_file_path -o output_file_path [--strict]\n";
    $reference .= "\n";
    $reference .= "Options:\n";
    $reference .= "  -h --help                                    Show this screen.\n";
    $reference .= "  -i | --imput      input_file_path            Path to imput csv file.\n";
    $reference .= "  -c | --config     configuration_file_path    Path to configuration .php file.\n";
    $reference .= "  -o | --output     output_file_path           Path to output csv file.\n";
    $reference .= "  -d | --delimiter  delimiter                  Change standard delimiter for csv files.\n";
    $reference .= "  --skip-first                                 Skip the first row as a header\n";
    $reference .= "  --strict                                     Verify that the source file contains the ";
    $reference .= " required number of columns described in the configuration file.\n";
    echo $reference;
    exit(0);
}

//функция вывода ошибки и выхода из программы с кодом 1
function getError($errors)
{
    echo " \n";
    foreach ($errors as $error) {
        echo $error . "\n";
    }
    echo "Для просмотра справки, используйте команду: \"csv_converter.php.php -h|--help\" \n";
    exit(1);
}
