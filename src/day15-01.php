<?php
declare(strict_types=1);

namespace Day15_01;

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
        return array_filter(explode(',', str_replace(PHP_EOL, ',', $input)));
    }
}

class Calculator
{
    private array $hash = [];

    public function __construct(public bool $isDebug = false)
    {
    }

    public function calculate(array $steps): int
    {
        $sums = array_map(
            [$this, 'hashFunction'],
            $steps);

        return array_sum($sums);
    }

    public function calculate2(array $steps): int
    {
        /** @var string[][] $boxes */
        $boxes = [];

        $powers = [];

        foreach ($steps as $step) {
            $step = trim($step, '-');

            [$lens, $focalLength] = array_pad(explode('=', $step), 2, null);

            $hash = $this->hashFunction($lens);

            if ($focalLength === null) {
                // remove lens
                // box not found
                if (!($boxes[$hash][$lens] ?? null)) {
                    continue;
                }


                unset($powers[$lens]);
                unset($boxes[$hash][$lens]);

                // recalculate power of lenses in the box
                $index = 0;
                foreach ($boxes[$hash] as $lens => $focalLength) {
                    $powers[$lens] = ($hash + 1) * ++$index * $focalLength;
                }
            } else {
                if (!isset($boxes[$hash])) {
                    $boxes[$hash] = [];
                }

                // adds lens
                if (isset($boxes[$hash][$lens])) {
                    $index = array_search($lens, array_keys($boxes[$hash]));
                } else {
                    $index = count($boxes[$hash]);
                }

                $boxes[$hash][$lens] = (int)$focalLength;

                $power = ($hash + 1) * ($index + 1) * $focalLength;

                $powers[$lens] = $power;
            }
        }

        return array_sum($powers);
    }

    /**
     * @param string $step
     *
     * @return int
     */
    public function hashFunction(string $step): int
    {
        if (isset($this->hash[$step])) {
            return $this->hash[$step];
        }
        $num = 0;
        foreach (str_split($step) as $char) {
            $num += ord($char);
            $num *= 17;
            $num %= 256;
        }

        return $this->hash[$step] = $num;
    }
}


$isDebug = false;

$input = Reader::getInput('day_15.txt');

/** @var string[] $lines */
$steps = Parser::parseInput($input, $isDebug);

$calculator = new Calculator($isDebug);

echo $calculator->calculate($steps) . PHP_EOL;

$calculator = new Calculator($isDebug);

echo $calculator->calculate2($steps) . PHP_EOL;
