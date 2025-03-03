<?php
declare(strict_types=1);

namespace Day18_01;

use Exception;

function human_bytes(int $bytes): string
{
    if ($bytes < 1) return '0';

    $units = ['B', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb'];
    $factor = min(floor(log($bytes, 1024)), 5);
    $value = round($bytes / (1024 ** $factor), $factor > 1 ? 2 : 0);

    return $value . $units[$factor];
}

class Reader
{
    public static function getInput($fileName): string
    {
        $filepath = __DIR__ . "/../in/$fileName";
        $input = trim(\file_get_contents($filepath));

        if (!$input) {
            throw new Exception("error reading file $filepath");
        }

        return $input;
    }
}

class Parser
{
    /**
     * @param string $input
     * @param bool $isDebug
     *
     * @return array{int[], int, int, string}
     */
    public static function parseInput(string $input, bool $isDebug = false): array
    {
        return array_map(
            fn($line) => [
                ($line = explode(' ', $line))[0],
                (int)$line[1],
                $line[2],
            ],
            explode(PHP_EOL, trim($input))
        );
    }
}

class Direction
{
    public const UP = [0, -1];

    public const DOWN = [0, 1];

    public const RIGHT = [1, 0];

    public const LEFT = [-1, 0];

    public const CONVERT = [
        'U' => self::UP,
        'D' => self::DOWN,
        'R' => self::RIGHT,
        'L' => self::LEFT,
    ];

    /**
     * @param string $char
     *
     * @return int[]
     */
    public static function charToDirection(string $char): array
    {
        return self::CONVERT[$char];
    }
}


class Calculator
{
    public function __construct(private bool $isDebug)
    {
    }

    /**
     * @return \Closure
     */
    public static function parsePart1(): \Closure
    {
        /**
         * @param array{string, int, string} $line
         *
         * @return array{string, int}
         */
        return function (array $line) {
            [$operation, $len, $color] = $line;

            return [$operation, $len];
        };
    }

    /**
     * @return \Closure
     */
    public static function parsePart2(): \Closure
    {
        /**
         * @param array{string, int, string} $line
         *
         * @return array{string, int}
         */
        return function (array $line) {
            [$operation, $len, $color] = $line;

            $color = str_replace(['(', ')', '#'], '', $color);

            $len = (int)hexdec(substr($color, 0, 5));
            $operation = 'RDLU'[$color[5]];

            return [$operation, $len];
        };
    }

    /**
     * @param array $digPlan
     * @param \Closure $parseLine
     *
     * @return int
     */
    public function calculate(array $digPlan, \Closure $parseLine): int
    {
        if ($this->isDebug) {
            $dot = [0, 0];
            $dots = [];
        }

        $x = 0;
        $area = 1;

        foreach ($digPlan as $line) {
            [$operation, $len] = $parseLine($line);

            if ($this->isDebug) {
                $dir = Direction::charToDirection($operation);
                $dot = [
                    $dot[0] + $dir[0] * $len,
                    $dot[1] + $dir[1] * $len,
                ];

                $dots[] = $dot;
            }

            switch ($operation) {
                case 'R':
                    $x += $len; // move to the right
                    $area += $len; // add the line length to saturated points area
                    break;
                case 'L':
                    $x -= $len; // move to the left, the saturated points were already added
                    break;
                case 'D':
                    $area += $x * $len + $len; // add the rectangle to the area, add one more line vertical line
                    break;
                case 'U':
                    $area -= $x * $len; // cut rectangle from already filled area
            }
        }

        if ($this->isDebug) {
            $offsetX = -min(array_column($dots, 0));
            $maxX = max(array_column($dots, 0)) + 1;
            $offsetY = -min(array_column($dots, 1));
            $maxY = max(array_column($dots, 1)) + 1;

            $field = array_fill(0, $maxY + $offsetY, str_repeat('.', $maxX + $offsetX));

            array_unshift($dots, [0, 0]);

            for ($index = 1; $index < count($dots); $index++) {
                $dot0 = $dots[$index - 1];
                $dot1 = $dots[$index];

                $dx = $dot1[0] - $dot0[0];
                for ($step = $dx > 0 ? -1 : 1; $dx; $dx += $step) {
                    $field[$dot0[1] + $offsetY][$dot0[0] + $dx + $offsetX] = '#';
                }

                $dy = $dot1[1] - $dot0[1];
                for ($step = $dy > 0 ? -1 : 1; $dy; $dy += $step) {
                    $field[$dot0[1] + $dy + $offsetY][$dot0[0] + $offsetX] = '#';
                }
            }

            $field = implode(PHP_EOL, $field);

            $this->showField($field);
        }

        return $area;
    }

    private function showField(string $field): void
    {
        echo chr(27) . chr(91) . 'H' . chr(27) . chr(91) . 'J';   //^[H^[J

        //$text = implode(PHP_EOL, $field);
        $text = $field;

        $text = str_replace(
            '#',
            "\e[0;31m#\e[0m",
            $text
        );

        echo PHP_EOL . $text . PHP_EOL;
    }
}

$isDebug = false;

$start = microtime(true);
$mem = memory_get_usage();

$input = Reader::getInput('day_18.txt');

$digPlan = Parser::parseInput($input, $isDebug);

$calculator = new Calculator($isDebug);

echo $calculator->calculate($digPlan, $calculator->parsePart1()) . PHP_EOL;
echo $calculator->calculate($digPlan, $calculator->parsePart2()) . PHP_EOL;

$end = microtime(true);

echo PHP_EOL . 'Memory: ' . human_bytes(memory_get_usage() - $mem) . PHP_EOL;
printf("Exec time: %.4f\n", $end - $start);

// 40131
// 104454050898331
