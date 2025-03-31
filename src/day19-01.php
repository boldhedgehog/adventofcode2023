<?php
declare(strict_types=1);

namespace Day19_01;

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
     * @return array{array<string, array>, int[]}
     */
    public static function parseInput(string $input, bool $isDebug = false): array
    {
        $xmas = str_split('xmas');
        $xmasKeys = array_flip($xmas);

        [$workflows, $parts] = explode(PHP_EOL . PHP_EOL, $input);

        $workflows = array_map(
            static function (string $line) use ($xmasKeys) {
                [$name, $rules] = explode('{', $line);
                $rules = trim($rules, '}');

                $rules = array_map(
                    static function (string $rule) use ($xmasKeys) {
                        [$condition, $target] = array_pad(explode(':', $rule), 2, null);

                        if (!$target) {
                            $target = $condition;
                            $condition = null;
                        } else {
                            $condition = [
                                $xmasKeys[$condition[0]],
                                $condition[1],
                                (int)(explode($condition[1], $condition))[1]
                            ];
                        }

                        return [$target, $condition];
                    },
                    explode(',', $rules)
                );

                return [$name, $rules];
            },
            explode(PHP_EOL, trim($workflows))
        );

        $workflows = array_column($workflows, 1, 0);

        $parts = array_map(
            fn($line) => array_map(
                fn($rating) => (int)str_replace([...$xmas, '=', '{', '}'], '', $rating),
                explode(',', $line)
            ),
            explode(PHP_EOL, trim($parts))
        );

        return [$workflows, $parts];
    }
}

class Calculator
{
    private array $path = [];
    private array $paths = [];

    public function __construct(private bool $isDebug, private readonly array $workflows)
    {
    }

    /**
     * @param array $parts
     *
     * @return int
     */
    public function calculate(array $parts): int
    {
        $result = 0;

        foreach ($parts as $part) {
            $path = [];

            if ($this->isDebug) {
                echo implode(',', $part) . ': ';
            }

            $target = $this->getPartTarget($part, $path);

            if ($this->isDebug) {
                $path[] = "\e[0;31m{$target}\e[0m";

                echo implode(' -> ', $path) . PHP_EOL;
            }

            if ($target == 'A') {
                $result += array_sum($part);
            }
        }

        if ($this->isDebug) {
            echo PHP_EOL;
        }

        return $result;
    }

    private function getPartTarget(array $part, array &$path): string
    {
        for ($target = 'in'; $target !== 'A' && $target !== 'R';) {
            if ($this->isDebug) {
                $path[] = $target;
            }

            $workflow = $this->workflows[$target];

            foreach ($workflow as $step) {
                [$target, $condition] = $step;

                if (!$condition) {
                    break;
                }

                [$index, $op, $value] = $condition;

                $partValue = $part[$index];

                if (($op == '<' && $partValue < $value) || ($op == '>' && $partValue > $value)) {
                    break;
                }
            }
        }

        return $target;
    }

    /**
     * @param string $target
     * @param int[][] $intervals
     *
     * @return int
     */
    public function calculateRecursive(string $target, array $intervals): int
    {
        $this->path[] = $target;

        //reached the Reject target or out of bounds
        if ($target == 'R' || array_filter($intervals, fn($interval) => $interval[0] >= $interval[1])) {
            $this->paths = $this->path;
            $this->path = [$this->path[0]];

            return 0;
        }

        // reached the Accept target
        if ($target == 'A') {
            $variations = array_product(
                array_map(
                    fn($interval) => $interval[1] - $interval[0],
                    $intervals
                )
            );

            $this->paths = $this->path;
            $this->path = [$this->path[0]];

            return $variations;
        }

        $result = 0;

        $workflow = $this->workflows[$target];

        foreach ($workflow as $step) {
            [$target, $condition] = $step;

            // for unconditional targets, redirect to the target with current intervals
            if (!$condition) {
                return $result + $this->calculateRecursive($target, $intervals);
            }

            [$index, $op, $value] = $condition;

            // interval to check
            [$start, $stop] = $intervals[$index];

            $diveIntervals = $intervals;

            if ($op == '<') {
                $diveIntervals[$index] = [$start, min($stop, $value)]; // recursive check interval
                $intervals[$index] = [max($start, $value), $stop]; // current interval for further checks on this level
            } else {
                $diveIntervals[$index] = [max($start, $value + 1), $stop]; // recursive check interval
                $intervals[$index] = [$start, min($stop, $value + 1)]; // current interval for further checks on this level
            }

            $result += $this->calculateRecursive($target, $diveIntervals);
        }

        return $result;
    }
}

$isDebug = true;

$start = microtime(true);
$mem = memory_get_usage();

$input = Reader::getInput('day_19.txt');

[$workflows, $parts] = Parser::parseInput($input, $isDebug);

$calculator = new Calculator($isDebug, $workflows);

echo $calculator->calculate($parts) . PHP_EOL;

echo $calculator->calculateRecursive('in', array_fill(0, 4, [1, 4001])) . PHP_EOL;

$end = microtime(true);

echo PHP_EOL . 'Memory: ' . human_bytes(memory_get_usage() - $mem) . PHP_EOL;
printf("Exec time: %.4f\n", $end - $start);

// 40131
// 104454050898331

//part 2 test = 167409079868000
//part 2 = 136661579897555
