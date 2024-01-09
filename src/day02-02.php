<?php

$games = trim(\file_get_contents(__DIR__ . '/../in/day_02.txt'));

$games = array_map(
    function ($line) {
        [$name, $tries] = explode(':', $line);
        $game = [];
        foreach (preg_split('/[;,]+/', $tries) as $cube) {
            [$qty, $color] = explode(' ', trim($cube));
            $game[$color] = max($game[$color] ?? 0, $qty);
        }

        return array_product($game);
    },
    explode(PHP_EOL, $games)
);

var_export($games);

echo array_sum($games) . PHP_EOL;
