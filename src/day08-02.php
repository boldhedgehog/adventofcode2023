<?php

namespace Day08_02;

$input = trim(\file_get_contents(__DIR__ . '/day_08.txt'));

if (!$input) {
    die('error');
}

$lines = explode(PHP_EOL, $input);

$steps = array_map(
    'intval',
    str_split(strtr(array_shift($lines), ['L' => 0, 'R' => 1]))
);
array_shift($lines);

$lines = array_map(
    function ($line) {
        [$key, $directions] = explode('=', $line);

        return [
            'key' => $key,
            'dest' => explode(',', $directions),
        ];
    },
    str_replace(['(', ')', ' '], '', $lines)
);

$nodes = array_column($lines, 'dest', 'key');

unset($lines, $input);

$initialStarts = $starts = array_values(array_filter(array_keys($nodes), fn($key) => $key[2] === 'A'));
$destinations = array_values(array_filter(array_keys($nodes), fn($key) => $key[2] === 'Z'));
$destinations = array_combine($destinations, $destinations);

//$initialStarts = $starts = array_slice($starts, 0, 3);

echo count($nodes) . ' / ' . count($steps) . PHP_EOL;
echo json_encode($starts) . ' > ' . json_encode($destinations) . PHP_EOL;

$reachedIteration = [];

foreach ($starts as $key => $start) {
    $leftRight = reset($steps);
    $reached = false;

    for ($iteration = 0; !$reached; $iteration++) {
        $nextNode = $nodes[$start][$leftRight];

        $reached = isset($destinations[$nextNode]);

// 38397 - 19198 = 19199

        $start = $nextNode;

        $leftRight = next($steps);
        if ($leftRight === false) {
            $leftRight = reset($steps);
        }
    }

    echo "$key $iteration" . PHP_EOL;
    $reachedIteration[$key] = $iteration;
}

unset($starts, $steps, $historyStack);

function gcd($a, $b)
{
    return ($a % $b) ? gcd($b, $a % $b) : $b;
}

function lcm(array $numbers)
{
// Initialize result
    $ans = $numbers[0];

    $count = count($numbers);
    for ($i = 1; $i < $count; $i++) {
        $ans = ((($numbers[$i] * $ans)) / (gcd($numbers[$i], $ans)));
    }

    return $ans;
}

//2: 1516721
//3: 107687191
var_export(lcm($reachedIteration));
