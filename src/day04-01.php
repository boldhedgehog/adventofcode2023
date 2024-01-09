<?php

$cards = trim(\file_get_contents(__DIR__ . '/../in/day_04.txt'));

$cards = array_map(
    function ($line) {
        [$name, $numbers] = explode(':', $line);
        [$winningNumbers, $numbers] = explode('|', $numbers);
        $winningNumbers = array_unique(array_filter(array_map('intval', explode(' ', trim($winningNumbers)))));
        $numbers = array_unique(array_filter(array_map('intval', explode(' ', trim($numbers)))));

        $matched = array_intersect($winningNumbers, $numbers);

        return $matched ? (1 << (count($matched) - 1)) : 0;
    },
    explode(PHP_EOL, $cards)
);

var_export($cards);

var_export(array_sum($cards));
