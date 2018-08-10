<?php

return [
    0 => 'streetName', // faker
    2 => 'randomDigit',
    3 => function ($value, $rowData, $rowIndex, $faker) {
        return $faker->randomDigit;
    },
];