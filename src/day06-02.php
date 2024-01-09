<?php

namespace Day06_02;

$input = trim(\file_get_contents(__DIR__ . '/../in/day_06.txt'));

$input = array_map(
    function ($line) {
        [,$numbers] = explode(':', $line);
        return (int)$numbers;
    },
    explode(PHP_EOL, str_replace(' ', '', $input))
);

var_export($input);

$times = [$input[0]];
$distances = [$input[1]];

unset($input);

$combinations = [];

foreach ($times as $race => $time) {
    $half = $time / 2;
    $firstRecordTime = 0;
    for ($hold = 1; $hold <= $half; $hold++) {
        $timeToRace = $time - $hold;
        $distance = $hold * $timeToRace;
        if ($distance > $distances[$race]) {
            $firstRecordTime = $hold;
            break;
        }
    }

    $combinations[] = $time - $firstRecordTime * 2 + 1;
}

var_export($combinations);

var_export(array_product($combinations));
