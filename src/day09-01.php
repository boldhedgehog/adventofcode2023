<?php

namespace Day09_01;


$lines = Parser::parseInput(Reader::getInput('day_09.txt'));

$sum = 0;

foreach ($lines as $line) {
    // for part 2
    $line = array_reverse($line);
    $sum += Calculator::calculate($line);
}

echo $sum . PHP_EOL;

class Calculator
{
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
     * @return array<int, int[]>
     */
    public static function parseInput($input): array
    {
        return array_map(
            fn($line) => array_map('intval', explode(' ', $line)),
            explode(PHP_EOL, $input)
        );
    }
}
