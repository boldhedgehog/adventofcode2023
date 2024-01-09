<?php

namespace Day11_01;

$lines = Parser::parseInput(Reader::getInput('day_11.txt'));

$xAxis = array_merge(... $lines);
sort($xAxis);
// multiply by count
$yAxis = [];
foreach ($lines as $key => $line) {
    $yAxis[] = array_fill(0, count($line), $key);
}

$yAxis = array_merge(... $yAxis);

// part 1
var_export(Calculator::sum($xAxis) + Calculator::sum($yAxis));

class Calculator
{
    public static function sum($dots)
    {
        $expansion = 0;
        // part 1
//        $expansionSize = 2;
        // part 2
        $expansionSize = 1000000;
        $prev = reset($dots);
        $startCoefficient = -count($dots) + 1;
        $sum = 0;
        foreach ($dots as $dot) {
            $diff = $dot - $prev;
            if ($diff > 1) {
                $expansion += ($expansionSize - 1) * ($diff - 1);
            }

            $prev = $dot;

            $sum += $startCoefficient * ($expansion + $dot);

            $startCoefficient += 2;
        }

        return $sum;
    }
}

class Reader
{
    public static function getInput($fileName): string
    {
        $filepath = __DIR__ . "/$fileName";
        $input = trim(\file_get_contents($filepath));

        if (!$input) {
            throw new \Exception("error reading file $filepath");
        }

        return $input;
    }
}

class Parser
{
    /**
     * @param string $input
     *
     * @return int[][]
     */
    public static function parseInput($input): array
    {
        $array = array_map(
            fn($line) => array_keys(array_filter(str_split($line), fn($char) => $char == '#')),
            explode(PHP_EOL, $input)
        );

        return array_filter($array);
    }
}
