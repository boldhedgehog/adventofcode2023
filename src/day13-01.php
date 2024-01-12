<?php
declare(strict_types=1);

namespace Day13_01;

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
     *
     * @return array<int, array<int, int[]>>
     */
    public static function parseInput(string $input, bool $isDebug = false): array
    {
        return array_map(
            function (string $pattern) use ($isDebug) {
                return [
                    self::mapStraight($pattern),
                    self::mapTransposed($pattern),
                    $isDebug ? $pattern : null
                ];
            },
            explode(PHP_EOL . PHP_EOL, $input)
        );
    }

    private static function mapStraight($pattern): array
    {
        return array_map(
            function ($line) {
                $number = str_replace(['.', '#'], [0, 1], $line);

                return bindec($number);
            },
            explode(PHP_EOL, $pattern)
        );
    }

    private static function mapTransposed($pattern): array
    {
        $matrix = array_map(
            function ($line) {
                $line = str_replace(['.', '#'], [0, 1], $line);

                return str_split($line);
            },
            explode(PHP_EOL, $pattern)
        );

        // flip matrix
        $matrix = array_map(null, ... $matrix);

        return array_map(
            function ($array) {
                return bindec(implode('', $array));
            },
            $matrix
        );
    }
}


class Calculator
{
    protected bool $requiredSmudges = false;

    public function __construct(public bool $isDebug = false)
    {
    }

    public function calculate($lines): int
    {
        $reflections = array_map([$this, 'calculatePattern'], $lines);

        return (int)array_sum($reflections);
    }

    public function calculateWithSmudges($lines): int
    {
        $this->requiredSmudges = true;

        $reflections = array_map([$this, 'calculatePattern'], $lines);

        return (int)array_sum($reflections);
    }

    /**
     * @param array{int[], int[], string|null} $pattern
     *
     * @return int
     * @throws Exception
     */
    protected function calculatePattern(array $pattern): int
    {
        [$horizontal, $vertical] = $pattern;

        $horizontalReflection = $this->calculateReflection($horizontal);
        if ($this->isDebug) {
            $this->debugOutputHorizontal($horizontalReflection, end($pattern));
            echo PHP_EOL;
        }

        $verticalReflection = $this->calculateReflection($vertical);

        if ($this->isDebug) {
            $this->debugOutputVertical($verticalReflection, end($pattern));
            echo PHP_EOL . '====' . PHP_EOL;
        }

        $reflection = 100 * $horizontalReflection + $verticalReflection;

        if (!$reflection) {
            throw new Exception(sprintf('No match [%s | %s]', implode(',', $horizontal), implode(',', $vertical)));
        }

        return $reflection;
    }

    protected function calculateReflection($originalNumbers, int $start = 0): int
    {
        $numbers = $originalNumbers;
        $aboveTheFold = $nextAboveTheFold = 0;
        $count = count($numbers);
        $foundSmudged = 0;

        for ($forward = $start, $reverse = $start + 1, $gear = 1; $forward < $count && $reverse >= 0 && $reverse < $count; $forward++, $reverse += $gear) {
            $eq = $this->eq($numbers[$forward], $numbers[$reverse]);
            // if smudged ia already found, ignore it
            $smudged = $this->requiredSmudges && $this->eqSmudged($numbers[$forward], $numbers[$reverse]);
            $foundSmudged += (int)$smudged;
            if ($gear == 1) {
                // found two matched numbers or two matched with smudges
                if ($eq || $foundSmudged == 1) {
                    // start looking for next pairs from the second duplicate's position
                    $aboveTheFold = $reverse;
                    if ($this->requiredSmudges) {
                        $numbers[$reverse] = $numbers[$forward];
                    } else {
                        $nextAboveTheFold = $this->calculateReflection($numbers, $reverse);
                    }
                    $gear = -1;
                    $forward++;
                    $reverse--;
                }
            } else {
                if (!$eq) {
                    if ($this->requiredSmudges) {
                        if (!$smudged || $foundSmudged != 1) {
                            return $this->calculateReflection($originalNumbers, $aboveTheFold);
                        }
                    } else {
                        return $nextAboveTheFold;
                    }
                }
            }
        }

        if ($this->requiredSmudges) {
            if ($foundSmudged) {
                return max($aboveTheFold, $nextAboveTheFold);
            } else {
                return ($aboveTheFold ? $this->calculateReflection($originalNumbers, $aboveTheFold) : $nextAboveTheFold);
            }
        } else {
            return max($aboveTheFold, $nextAboveTheFold);
        }
    }

    protected function eq($x, $y): bool
    {
        return $x === $y;
    }

    protected function eqSmudged($x, $y): bool
    {
        $xor = $x ^ $y;

        return $xor && !($xor & ($xor - 1));
    }

    /**
     * @param bool|int $reflection
     * @param mixed $original
     *
     * @return void
     */
    protected function debugOutputHorizontal(bool|int $reflection, string $original): void
    {
        if ($reflection) {
            $original = explode(PHP_EOL, $original);
            $count = count($original);
            $linePos = 2 * $reflection - $count; // a+b=c; pos=a-b; b=c-a; pos=2a-c;
            if ($linePos < 0) {
                $linePos = $count + $linePos;
            }
            $original = array_map(
                function ($row, $index) use ($reflection, $linePos) {
                    $separatorLine = ($index == $linePos) ? str_repeat('-', strlen($row) + 2) . PHP_EOL : '';

                    $index++;

                    $arrow = $index == $reflection ? 'V' : ($index == $reflection + 1 ? "^ $reflection" : ' ');

                    return "$separatorLine{$row} {$arrow}";
                },
                $original,
                array_keys($original)
            );
            echo implode(PHP_EOL, $original) . PHP_EOL;
        } else {
            echo $original . PHP_EOL;
            echo 'no H reflection' . PHP_EOL;
        }
    }

    /**
     * @param bool|int $reflection
     * @param string $original
     *
     * @return void
     */
    protected function debugOutputVertical(bool|int $reflection, string $original): void
    {
        if ($reflection) {
            $original = explode(PHP_EOL, $original);
            $original = array_map(
                function ($row) use ($reflection) {
                    $linePos = 2 * $reflection - strlen($row); // a+b=c; pos=a-b; b=c-a; pos=2a-c;

                    return ' ' . substr($row, 0, $linePos) . '|' . substr($row, $linePos);
                },
                $original
            );

            $arrow = str_pad((string)$reflection, $reflection + (int)($reflection > strlen($original[0]) / 2)) . '><';
            echo $arrow . PHP_EOL;
            echo implode(PHP_EOL, $original) . PHP_EOL;
            echo $arrow . PHP_EOL;
        } else {
            echo $original . PHP_EOL;
            echo 'no V reflection' . PHP_EOL;
        }
    }
}

$isDebug = false;

/** @var array<int, array<int, int[]>> $patterns */
$patterns = Parser::parseInput(Reader::getInput('day_13.txt'), $isDebug);

//if ($isDebug) {
//    var_export($patterns);
//}
// test: 405
// 34993

$calculator = new Calculator($isDebug);

echo $calculator->calculate($patterns) . PHP_EOL;

echo PHP_EOL . ' ==== Smudged' . PHP_EOL;
// test: 400
// 29341
echo $calculator->calculateWithSmudges($patterns) . PHP_EOL;
