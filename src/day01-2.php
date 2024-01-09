<?php

$lines = trim(\file_get_contents(__DIR__ . '/../in/day_01.txt'));

$sum = 0;

$digits = [
    1 => 'one',
    'two',
    'three',
    'four',
    'five',
    'six',
    'seven',
    'eight',
    'nine',
];

$reversedDigits = array_map('strrev', $digits);

foreach (explode(PHP_EOL, $lines) as $lineNo => $line) {
    $transLine = strtr($line, array_flip($digits));
    $lineSum = 10 * lineFirstDigit($transLine);

    $transLineRev = strtr(strrev($line), array_flip($reversedDigits));
    $lineSum += lineFirstDigit($transLineRev);

    echo "$lineNo : $lineSum : $line => $transLine , $transLineRev" . PHP_EOL;
    $sum += $lineSum;
}

echo $sum . PHP_EOL;

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
