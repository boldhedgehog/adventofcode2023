<?php

$lines = trim(\file_get_contents(__DIR__ . '/../in/day_01.txt'));

$sum = 0;

/**
 * @param string $line
 *
 * @return int
 */
function lineFirstDigit(string $line): int
{
    $strlen = strlen($line);
    for ($i = 0; $i < $strlen; $i++) {
        $digit = (int)$line[$i];
        if ($digit > 0) {
            return $digit;
        }
    }

    return 0;
}

foreach (explode(PHP_EOL, $lines) as $lineNo => $line) {
    $lineSum = 10 * lineFirstDigit($line) + lineFirstDigit(strrev($line));
    echo "$lineNo : $lineSum : $line" . PHP_EOL;
    $sum += $lineSum;
}

echo $sum . PHP_EOL;
