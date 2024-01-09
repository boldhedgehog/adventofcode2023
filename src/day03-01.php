<?php

$scheme = trim(\file_get_contents(__DIR__ . '/../in/day_03.txt'));

$rowNum = 0;

$symbolMatrix = [];

$matrix = array_map(
    function ($line) use (&$rowNum, &$symbolMatrix) {
        $row = [];
        $strlen = strlen($line);
        $num = 0;
        for ($i = 0; $i < $strlen; $i++) {
            $char = $line[$i];
            if (is_numeric($char)) {
                $digit = (int)$char;
                $num = $num * 10 + $digit;
            } else {
                if ($num > 0) {
                    $row = array_pad($row, count($row) + strlen($num), $num);
                    $num = 0;
                }

                $row[] = false;
                if ($char !== '.') {
                    // fill the symbol matrix
                    for ($symRow = $rowNum - 1; $symRow <= $rowNum + 1; $symRow++) {
                        $symbolMatrix['has'][$symRow] = true;
                        for ($symCol = $i - 1; $symCol <= $i + 1; $symCol++) {
                            $symbolMatrix['matrix'][$symRow][$symCol] = 1;
                        }
                    }
                }
            }
        }

        if ($num > 0) {
            $row = array_pad($row, $strlen, $num);
        }

        $rowNum++;

        return array_filter($row);
    },
    explode(PHP_EOL, $scheme)
);

$parts = [];

$sum = 0;

foreach ($matrix as $rowNum => $row) {
    if (!$symbolMatrix['has'][$rowNum]) {
        continue;
    }

    $prev = null;
    $symbolRow = $symbolMatrix['matrix'][$rowNum];
    foreach ($row as $colNum => $value) {
        if ($symbolRow[$colNum] ?? false) {
            // previous value was NOT this number
            if (!($prev !== null && $colNum - $prev == 1)) {
                $parts[$rowNum][] = $value;
                $sum += $value;
            }

            $prev = $colNum;
        }
    }
}

//var_export($matrix);
var_export($parts);
var_export($sum);
