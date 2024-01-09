<?php

namespace Day06_01;

$input = trim(\file_get_contents(__DIR__ . '/../in/day_06.txt'));

$input = array_map(
    function ($line) {
        $numbers = explode(' ', $line);
        array_shift($numbers);
        return array_map('intval', array_values(array_filter($numbers)));
    },
    explode(PHP_EOL, $input)
);

var_export($input) . PHP_EOL;

$times = $input[0];
$distances = $input[1];

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

var_export($combinations) . PHP_EOL;

var_export(array_product($combinations)) . PHP_EOL;
