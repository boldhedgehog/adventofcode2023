<?php
declare(strict_types=1);

namespace Day14_01;

use Exception;

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
     * @return array{int, array<int, array<int, string[]>>}
     */
    public static function parseInput(string $input, bool $isDebug = false): array
    {
        $lines = array_filter(explode(PHP_EOL, $input));
        if ($isDebug) {
            var_export($lines);
        }

        return [count($lines), self::map($lines)];
    }

    private static function map($lines): array
    {
        $matrix = array_map(
            function ($line) {
                $line = str_replace(['.', 'O'], [0, 1], $line);

                return str_split($line);
            },
            $lines
        );

        // flip matrix
        $matrix = array_map(null, ... $matrix);

        return array_map(
            function ($array) {
                return explode('#', implode('', $array));
            },
            $matrix
        );
    }
}


class Calculator
{
    private array $chunkHash = [];

    public function __construct(public bool $isDebug = false)
    {
    }

    public function calculate($count, $lines): int
    {
        $sum = 0;
        foreach ($lines as $chunks) {
            $base = $count;
            $nums = array_map(
                function ($chunk) use (&$base): int {
                    // strlen() + 1 to skip # that was removed by explode()
                    if ($chunk === '') {
                        $base--;

                        return 0;
                    } elseif ((int)$chunk === 0) {
                        $base -= strlen($chunk) + 1;

                        return 0;
                    }

                    $num = $this->sequenceSum($chunk, $base);
                    $base -= strlen($chunk) + 1;

                    return $num;
                },
                $chunks
            );

            $sum += array_sum($nums);

            if ($this->isDebug) {
                var_export($nums);
            }
        }

        return $sum;
    }

    public function sequenceSum(string $chunk, int $base): int
    {
        if (isset($this->chunkHash[$chunk])) {
            $ones = $this->chunkHash[$chunk];
        } else {
            $this->chunkHash[$chunk] = $ones = substr_count($chunk, '1');
        }

        for ($sum = 0; $ones; $ones--) {
            $sum += $base;
            $base--;
        }

        return $sum;
    }

    public function calculate2(string $input): int
    {
        $input = str_replace(['.', '#', 'O'], [0, 0, 1], $input);

        $lines = array_filter(explode(PHP_EOL, $input));

        $matrix = array_map(
            fn($line) => str_split($line),
            $lines
        );

        $matrix = array_map(null, ... $matrix);

        $nums = array_map(
            function (array $spaces) {
                if (array_sum($spaces) == 0) {
                    return 0;
                }
                $num = 0;
                $count = count($spaces);
                foreach ($spaces as $space) {
                    $num += $count * ($space);
                    $count--;
                }

                return $num;
            },
            $matrix
        );

        return array_sum($nums);
    }
}

class Hash
{
    public array $inputHash = [];
}

class Rotator
{
    private array $chunkHash = [];

    private array $lineHash = [];

    public function __construct(public bool $isDebug = false)
    {
    }

    public function cycle(string $input): string
    {
        $newInput = $this->tilt($input);
//        echo $newInput . PHP_EOL;
//        echo '---' . PHP_EOL;

        $newInput = $this->rotate($newInput);

//        echo 'Rotated 1:' . PHP_EOL . $newInput . PHP_EOL;
//        echo '===' . PHP_EOL;

        $newInput = $this->tilt($newInput);
//        echo $newInput . PHP_EOL;
//        echo '---' . PHP_EOL;

        $newInput = $this->rotate($newInput);

//        echo 'Rotated 2:' . PHP_EOL . $newInput . PHP_EOL;
//        echo '===' . PHP_EOL;

        $newInput = $this->tilt($newInput);
//        echo $newInput . PHP_EOL;
//        echo '---' . PHP_EOL;

        $newInput = $this->rotate($newInput);

//        echo 'Rotated 3:' . PHP_EOL . $newInput . PHP_EOL;
//        echo '===' . PHP_EOL;

        $newInput = $this->tilt($newInput);

//        echo $newInput . PHP_EOL;
//        echo '---' . PHP_EOL;

        $newInput = $this->rotate($newInput);

        if ($this->isDebug) {
            echo 'Rotated Final:' . PHP_EOL . str_replace([0, 1], ['.', 'O'], $newInput) . PHP_EOL;
            echo '===' . PHP_EOL;
        }

        return $newInput;
    }

    public function tilt(string $input): string
    {
        $lines = array_filter(explode(PHP_EOL, $input));

        $matrix = array_map(
            fn($line) => str_split($line),
            $lines
        );

        $matrix = array_map(null, ... $matrix);

        $lines = array_map(
            'implode',
            $matrix
        );

        $tiltedLines = $this->doTilt($lines);

        $matrix = array_map(
            fn($line) => str_split($line),
            $tiltedLines
        );

        $matrix = array_map(null, ... $matrix);

        $tiltedLines = array_map(
            'implode',
            $matrix
        );

        return implode(PHP_EOL, $tiltedLines);
    }

    public function rotate(string $input)
    {
        $lines = array_filter(explode(PHP_EOL, $input));

        $lines = array_reverse($lines);

        $matrix = array_map(
            fn($line) => str_split($line),
            $lines
        );

        $matrix = array_map(null, ... $matrix);

        $lines = array_map(
            'implode',
            $matrix
        );

        return implode(PHP_EOL, $lines);
    }

    /**
     * @param string[] $lines
     *
     * @return string[]
     */
    public function doTilt(array $lines): array
    {
        return array_map(
            function (string $line) {
                if (isset($this->lineHash[$line])) {
                    return $this->lineHash[$line];
                }

                return $this->lineHash[$line] = implode('#',
                    array_map(
                        function (string $chunk): string {
                            if ($chunk === '') {
                                return '';
                            } elseif ((int)$chunk === 0) {
                                return $chunk;
                            }

                            if (isset($this->chunkHash[$chunk])) {
                                return $this->chunkHash[$chunk];
                            }

                            return $this->chunkHash[$chunk] = $this->tiltChunk($chunk);
                        },
                        explode('#', $line)
                    )
                );
            },
            $lines);
    }

    public function tiltChunk(string $chunk): string
    {
        $stones = substr_count($chunk, '1');

        return str_pad(str_repeat('1', $stones), strlen($chunk), '0');
    }
}

$isDebug = false;

$input = Reader::getInput('day_14.txt');

/** @var array<int, array<int, int[]>> $lines */
[$count, $lines] = Parser::parseInput($input, $isDebug);

if ($isDebug) {
    echo var_export($count, true) . PHP_EOL;
    echo var_export($lines, true) . PHP_EOL;
}

$calculator = new Calculator($isDebug);

echo $calculator->calculate($count, $lines) . PHP_EOL;

unset($count, $lines);

$rotator = new Rotator(false);

$currentInput = str_replace(['.', 'O'], [0, 1], $input);

if ($isDebug) {
    echo 'Original Input:' . PHP_EOL . $input . PHP_EOL;
    echo PHP_EOL . PHP_EOL;
}

$hash = new Hash();
$cycles = 1_000_000_000;
//$cycles = 3;

for ($i = 0; $i < $cycles; $i++) {
    if (isset($hash->inputHash[$currentInput])) {
        $loopStart = array_search($currentInput, array_keys($hash->inputHash));
        $loopedRemainder = $cycles - $loopStart;
        $loopLen = $i - $loopStart;
        $endIteration = $loopedRemainder % $loopLen + $loopStart;
        echo "Got loop on $i, looped remainder $loopedRemainder, loop len $loopLen" . PHP_EOL;
        echo "Loop start: $loopStart" . PHP_EOL;
        echo "Final cached iteration: $endIteration" . PHP_EOL;

        break;
    }

    $newInput = $rotator->cycle($currentInput);

    if ($isDebug) {
        echo "After $i cycle:" . PHP_EOL . str_replace([0, 1], ['.', 'O'], $newInput) . PHP_EOL . PHP_EOL;
    }

    $hash->inputHash[$currentInput] = true;

    $currentInput = $newInput;
}

$finalInput = str_replace([0, 1], ['.', 'O'], array_keys($hash->inputHash)[$endIteration]);

if ($isDebug) {
    echo 'Final input:' . PHP_EOL . $finalInput . PHP_EOL;
    echo '===' . PHP_EOL;
}

/** @var array<int, array<int, int[]>> $lines */

echo $calculator->calculate2($finalInput) . PHP_EOL;
