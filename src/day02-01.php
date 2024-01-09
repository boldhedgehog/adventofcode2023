<?php

$games = trim(\file_get_contents(__DIR__ . '/../in/day_02.txt'));

$goodGame = [
    'red' => 12,
    'green' => 13,
    'blue' => 14,
];

$games = array_filter(
    explode(PHP_EOL, $games),
    function ($line) use ($goodGame) {
        [$name, $tries] = explode(':', $line);
        $tries = explode(';', $tries);
        $badTries = array_filter(
            $tries,
            function ($balls) use ($goodGame) {
                $badBalls = array_filter(
                    explode(',', $balls),
                    function ($ball) use ($goodGame) {
                        [$qty, $color] = explode(' ', trim($ball));

                        return !isset($goodGame[$color]) || $goodGame[$color] < (int)$qty;
                    }
                );

                return $badBalls;
            }
        );

        return !$badTries;
    }
);

var_export($games);

echo (array_sum(array_keys($games)) + count($games)) . PHP_EOL;
