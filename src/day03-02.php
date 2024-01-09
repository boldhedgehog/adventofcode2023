<?php

$scheme = trim(\file_get_contents(__DIR__ . '/../in/day_03.txt'));

$rowNum = 0;

$gearRows = [];
$gearMatrix = [];

$matrix = array_map(
    function ($line) use (&$rowNum, &$gearMatrix, &$gearRows) {
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
                if ($char === '*') {
                    // fill the symbol matrix
                    for ($symRow = $rowNum - 1; $symRow <= $rowNum + 1; $symRow++) {
                        $gearRows[$symRow] = true;
                        for ($symCol = $i - 1; $symCol <= $i + 1; $symCol++) {
                            // store in all adjacent cells the coordinates of the gear
                            $gearMatrix[$symRow][$symCol] = [$rowNum, $i];
                        }
                    }

                    // store 1 to the gear cell, overwrite it
                    $gearMatrix[$rowNum][$i] = 1;
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

$adjacentGearCells = [];

foreach ($matrix as $rowNum => $row) {
    if (!($gearRows[$rowNum] ?? null)) {
        continue;
    }

    $prev = null;
    $symbolRow = $gearMatrix[$rowNum];

    foreach ($row as $colNum => $partValue) {
        $gearCoords = $symbolRow[$colNum] ?? false;
        if ($gearCoords) {
            [$gearRow, $gearCol] = $gearCoords;
            // previous column value was NOT this number
            if (!($prev !== null && $colNum - $prev == 1)) {
                $adjacentGearCells["$gearRow:$gearCol"][] = $partValue;

                $parts[$rowNum][] = $partValue;
            }

            $prev = $colNum;
        }
    }
}

$adjacentGearCells = array_map(
    function (array $partValues) {
        return count($partValues) > 1 ? array_product($partValues) : 0;
    },
    $adjacentGearCells
);

//var_export($matrix);
var_export($parts);
var_export($adjacentGearCells);
var_export(array_sum($adjacentGearCells));
