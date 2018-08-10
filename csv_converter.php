<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions.php';

echo "CSV converter , made by Grey\n";

$shortopts = 'i:'; // параметр пути до исходного файла
$shortopts .= 'c:'; // параметр пути до файла конфигурации
$shortopts .= 'o:'; // параметр пути до файла с результатом
$shortopts .= 'd:'; // разделитель
$shortopts .= 'h';  // справка

$longopts = array(
    'input:',       // параметр пути до исходного файла
    'config:',      // параметр пути до файла конфигурации
    'output:',      // параметр пути до файла с результатом
    'delimiter:',   // разделитель
    'skip-first',   // пропускать редактирование первой строки
    'strict',       // проверка соответсвия столбцов в исходном файле и файле конфигурации
    'help',         // справка
);
$options = getopt($shortopts, $longopts);
//основной блок кода, где вызываются все остальные функции
switch (count($options)) {
    case 1:
        if (isset($options['h']) | isset($options['help'])) {
            getReference();
        } else {
            getError(['Ошибка параметра, передано неверное число параметров, воспользуйтесь справкой']);
        }
        break;
    case 3:
    case 4:
    case 5:
    case 6:
        list($skipFirst, $delimiter, $strict, $originalFilename, $confFilename, $newFilename) =
            argParse($options, $argc);

        $funcArray = loadConfFile($confFilename);

        $EOL = detectEOL($originalFilename);

        list($encoding, $data) = loadOriginFile($originalFilename, $delimiter, $EOL);

        testStrict($data, $funcArray);

        $newArray = CreateNewArray($data, $funcArray, $skipFirst);

        saveNewFile($newFilename, $newArray, $delimiter, $EOL, $encoding);

        echo "Файл был успешно сконвертирован \n";
        exit(0);
    default:
        getError(['Ошибка параметра, передано неверное число параметров, воспользуйтесь справкой']);
}
