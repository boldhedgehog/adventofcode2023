<?php

namespace Day10_01;

$lines = Parser::parseInput(Reader::getInput('day_10.txt'));

// y, x
$pos = Calculator::findStart($lines);

if (!$pos) {
    die('No start');
}

$currentVector = Calculator::getFirstVector($lines, $pos);
$iterations = 0;

$pathGrid = array_fill(0, count($lines), array_fill(0, count($lines[0]), ' '));

$dots = [];

do {
    $iterations++;
    $pos = Calculator::vadd($pos, $currentVector);
    $gridChar = $lines[$pos[0]][$pos[1]];
    $pathGrid[$pos[0]][$pos[1]] = $gridChar;

    $dots[] = $pos;

    if ($gridChar == 'S') {
        break;
    }

    $currentVector = Calculator::vadd($currentVector, Calculator::$directions[$gridChar]);
} while ($gridChar != 'S');

// Pick's Theorem https://en.wikipedia.org/wiki/Pick%27s_theorem
// area = innerDots + outerDots/2 - 1
// innerDots = area - outerDots/2 + 1

$area = Calculator::gaussArea($dots);

$innerDots = $area - count($dots)/2 + 1;

//echo implode(PHP_EOL, array_map(fn($line) => implode('', $line), $pathGrid));

echo $iterations / 2 . PHP_EOL;

echo "Inner: $innerDots". PHP_EOL;

class Calculator
{
    public static array $directions = [
        '|' => [0, 0],
        '-' => [0, 0],
        'L' => [-1, 1],
        'J' => [-1, -1],
        '7' => [1, -1],
        'F' => [1, 1],
    ];

    /**
     * @param int[][] $dots
     *
     * @return float|int
     */
    public static function gaussArea(array $dots): float|int
    {
        $count = count($dots) - 1;
        $sum = 0;
        for ($index = 0; $index < $count; $index++) {
            $sum += $dots[$index][0] * $dots[$index + 1][1] - $dots[$index][1] * $dots[$index + 1][0];
        }

        $sum += $dots[$index][0] * $dots[0][1] - $dots[$index][1] * $dots[0][0];

        return abs($sum) / 2;
    }

    public static function findStart($lines)
    {
        foreach ($lines as $y => $line) {
            foreach ($line as $x => $value) {
                if ($value == 'S') {
                    return [$y, $x];
                }
            }
        }

        return false;
    }

    public static function getFirstVector($grid, $pos)
    {
        foreach (range(-1, 1) as $y) {
            foreach (range(-1, 1) as $x) {
                if (abs($x ^ $y) != 1) {
                    continue;
                }

                $gridChar = $grid[$pos[0] + $y][$pos[1] + $x] ?? null;
                $vector = self::$directions[$gridChar];
                $newVector = self::vm($vector, [-$y, -$x]);

                if ($newVector == [$y, $x]) {
                    return [$y, $x];
                }
            }
        }
    }

    public static function vm($v1, $v2)
    {
        return [
            $v1[0] * $v2[0],
            $v1[1] * $v2[1],
        ];
    }

    public static function vadd($v1, $v2)
    {
        return [
            $v1[0] + $v2[0],
            $v1[1] + $v2[1],
        ];
    }

    /**
     * @param int[] $coord
     * @param int[] $direction
     *
     * @return void
     */
    public static function move(array $coord, array $direction)
    {
    }

    /**
     * @param int[] $numbers
     *
     * @return int
     */
    public static function calculate(array $numbers): int
    {
        $result = 0;
        $oddEven = 1;
        $kFact = self::fact(count($numbers));

        foreach ($numbers as $i => $number) {
            // n! / (k! * (n - k)!)
            $coefficient = $i == 0 ? 1 : $kFact / self::fact($i) / self::fact(count($numbers) - $i);

            $sign = 1 - 2 * ($oddEven ^= 1);
            $result += $sign * $coefficient * $number;
        }

        return $result;
    }

    public static function fact(int $n): int|float
    {
        if ($n <= 1) {
            return 1;
        } elseif ($n == 2) {
            return 2;
        }

        for ($fact = $n; $n > 2;) {
            $fact *= --$n;
        }

        return $fact;
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
     * @return array<int, \SplFixedArray>
     */
    public static function parseInput($input): array
    {
        $array = array_map(
            fn($line) => \SplFixedArray::fromArray(str_split($line), false),
            explode(PHP_EOL, $input)
        );

        return $array;
    }
}
