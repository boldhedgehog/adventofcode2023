<?php

$cards = trim(\file_get_contents(__DIR__ . '/../in/day_04.txt'));

$matchedCards = array_map(
    function ($line) {
        [$name, $numbers] = explode(':', $line);
        [$winningNumbers, $numbers] = explode('|', $numbers);
        $winningNumbers = array_unique(array_filter(array_map('intval', explode(' ', trim($winningNumbers)))));
        $numbers = array_unique(array_filter(array_map('intval', explode(' ', trim($numbers)))));

        $matched = array_intersect($winningNumbers, $numbers);

        return count($matched);
    },
    explode(PHP_EOL, $cards)
);

$maxIndex = count($matchedCards) - 1;
$cardsCount = array_pad([], count($matchedCards), 1);

foreach ($matchedCards as $index => $matches) {
    for ($i = 0; $i < $matches; $i++) {
        $nextIndex = $i + $index + 1;
        if ($nextIndex > $maxIndex) {
            continue(2);
        }

        $cardsCount[$nextIndex] += $cardsCount[$index];
    }
}

var_export($matchedCards);
var_export($cardsCount);
var_export(array_sum($cardsCount));
