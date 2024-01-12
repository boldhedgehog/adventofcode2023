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

    private static function mapTransposed($pattern)
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
    protected const MAX_RECURSION = 2;

    protected \Closure $matchingFunction;

    public function __construct(public bool $isDebug = false)
    {
    }

    public function calculate($lines): int
    {
        $this->matchingFunction = fn($x, $y) => $x === $y;

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

        $reflection = $this->calculateReflection($horizontal);
        if (!$reflection) {
            $reflection = $this->calculateReflection($vertical);

            if ($this->isDebug) {
                $this->debugOutputVertical($reflection, end($pattern));
            }
        } else {
            if ($this->isDebug) {
                $this->debugOutputHorizontal($reflection, end($pattern));
            }

            $reflection *= 100;
        }

        if ($this->isDebug) {
            echo '---' . PHP_EOL;
        }

        if (!$reflection) {
//            if (++$skip < self::MAX_RECURSION) {
//                if ($this->isDebug) {
//                    echo "Retrying with skipping $skip duplicates" . PHP_EOL;
//                }
//
//                return $this->calculatePattern($pattern, $skip);
//            }
            throw new Exception(sprintf('No match [%s, %s]', implode(',', $horizontal), implode(',', $vertical)));
        }

        return $reflection;
    }

    protected function calculateReflection($numbers, int $start = 0): int
    {
        $aboveTheFold = $nextAboveTheFold = 0;
        $count = count($numbers);
        for ($forward = $start, $reverse = $start + 1, $gear = 1; $forward < $count && $reverse >= 0 && $reverse < $count; $forward++, $reverse += $gear) {
            if ($gear == 1) {
                // found two matched numbers
                if (($this->matchingFunction)($numbers[$forward], $numbers[$reverse])) {
                    // start looking for next pairs from the second duplicate's position
                    $aboveTheFold = $reverse;
                    $nextAboveTheFold = $this->calculateReflection($numbers, $reverse);
                    $gear = -1;
                }
            } else {
                if (!($this->matchingFunction)($numbers[$forward], $numbers[$reverse])) {
                    return $nextAboveTheFold;
                }
            }
        }

        return max($aboveTheFold, $nextAboveTheFold);
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
}

$isDebug = true;

/** @var array<int, array<int, int[]>> $patterns */
$patterns = Parser::parseInput(Reader::getInput('day_13.txt'), $isDebug);

//if ($isDebug) {
//    var_export($patterns);
//}

// 36507
// 33385
// 14941
// 34993

$calculator = new Calculator($isDebug);

echo $calculator->calculate($patterns) . PHP_EOL;
