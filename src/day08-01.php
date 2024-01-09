<?php

namespace Day08_01;

$inputTest = <<<TXT
LLR

AAA = (BBB, BBB)
BBB = (AAA, ZZZ)
ZZZ = (ZZZ, ZZZ)
TXT;

$input = trim(\file_get_contents(__DIR__ . '/../in/day_08.txt'));

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

$start = 'AAA';
$dest = 'ZZZ';

echo count($nodes) . ' / ' . count($steps) . PHP_EOL;
echo "$start > $dest" . PHP_EOL;

$leftRight = reset($steps);
for ($iteration = 0; $start != $dest; $iteration++) {
    $nextNode = $nodes[$start][$leftRight];

    $dirs = json_encode($nodes[$start]);

//    echo "$start {$dirs}[$leftRight] > $nextNode | $iteration" . PHP_EOL;

    $start = $nextNode;

    $leftRight = next($steps);
    if ($leftRight === false) {
        $leftRight = reset($steps);
//        echo '---------' . PHP_EOL;
    }
}

var_export($iteration);
