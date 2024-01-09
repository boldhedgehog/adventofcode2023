<?php

namespace Day05_01;

$mapping = trim(\file_get_contents(__DIR__ . '/../in/day_05.txt'));

global $names;

function debugLevel($message, $level) {
    echo str_repeat("\t", $level + 1) . $message . PHP_EOL;
}

/**
 * @param int $number
 * @param array<int, array{dest: int, source: int, range: int, maxDest: int, maxSource: int}> $pins
 * @param int $index
 *
 * @return int
 */
function rollDown(int $number, array $pins, int $index = 0): int
{
    global $names;

    // reached the bottom
    if (!isset($pins[$index])) {
        debugLevel('Reached the bottom', $index);
        return $number;
    }

    debugLevel("Rolling down $names[$index] number $number", $index);

    $thisLevelPins = $pins[$index];
    /** @var array{dest: int, source: int, range: int, maxDest: int, maxSource: int} $pin */
    $pin = array_filter(
        $thisLevelPins,
        /**
         * @param array{dest: int, source: int, range: int, maxDest: int, maxSource: int} $pin
         * @param int $sourceStart
         *
         * @return bool
         */
        function (array $pin, int $sourceStart) use ($number) {
            return $sourceStart <= $number && $pin['maxSource'] >= $number;
        },
        ARRAY_FILTER_USE_BOTH
    );

    // if no mapping - dest is the same is number
    if ($pin) {
        $pin = reset($pin);
        $dest = (($number - $pin['source']) + $pin['dest']);
        debugLevel("found mapping: $dest " . json_encode($pin), $index);
    } else {
        $dest = $number;
        debugLevel("no mapping: $dest", $index);
    }

    return rollDown($dest, $pins, ++$index);
}

$blocks = explode(PHP_EOL . PHP_EOL, $mappingTest);

$names = [];

$seeds = explode(' ', array_shift($blocks));
array_shift($seeds);
$seeds = array_map('intval', $seeds);
sort($seeds);

$blocks = array_map(
    function (string $block) use (&$names) {
        $blockLines = explode(PHP_EOL, $block);
        $names[] = array_shift($blockLines);
        $maps = array_map(
            function ($line) {
                /** @var int[] $numbers */
                $numbers = explode(' ', $line);

                $numbers = array_map('intval', $numbers);

                [$dest, $source, $rangeLenght] = $numbers;

                return [
                    'dest' => $dest,
                    'source' => $source,
                    'rangeLength' => $rangeLenght,
                    'maxSource' => $source + $rangeLenght - 1,
                    'maxDest' => $dest + $rangeLenght - 1,
                ];
            },
            $blockLines
        );
        usort(
            $maps,
            function ($map1, $map2) {
                return $map1['source'] - $map2['source'];
            }
        );

        return array_combine(array_column($maps, 'source'), $maps);
    },
    $blocks
);

var_export($blocks);

$results = array_map(
    function ($seed) use ($blocks) {
        return rollDown($seed, $blocks, 0);
    },
    $seeds
);

var_export($results);

var_export(min($results));
