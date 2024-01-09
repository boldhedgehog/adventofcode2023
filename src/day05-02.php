<?php

namespace Day05_02;

$mapping = trim(\file_get_contents(__DIR__ . '/../in/day_05.txt'));

global $names;

const debug_level = 1;

function debugLevel($message, $level = 0)
{
    global $names;
    if (!debug_level) {
        return;
    }

    echo "{$names[$level]} " . str_repeat("\t", $level + 1) . $message . PHP_EOL;
}

$blocks = explode(PHP_EOL . PHP_EOL, $mapping);

$names = [];

$seeds = explode(' ', array_shift($blocks));
array_shift($seeds);
$seeds = array_map('intval', $seeds);
$seeds = array_chunk($seeds, 2);

$seeds = array_map(
    function ($seed) {
        $seed[] = $seed[0] + $seed[1] - 1;

        return $seed;
    },
    $seeds
);

usort(
    $seeds,
    function ($seed1, $seed2) {
        return $seed2[0] - $seed1[0];
    }
);

$blocks = array_map(
    function (string $block) use (&$names) {
        $blockLines = explode(PHP_EOL, $block);
        $names[] = array_shift($blockLines);
        $maps = array_map(
            function ($line) {
                /** @var int[] $numbers */
                $numbers = explode(' ', $line);

                $numbers = array_map('intval', $numbers);

                [$dest, $source, $rangeLength] = $numbers;

                return [
                    'dest' => $dest,
                    'source' => $source,
                    'rangeLength' => $rangeLength,
                    'maxSource' => $source + $rangeLength - 1,
                    'maxDest' => $dest + $rangeLength - 1,
                ];
            },
            $blockLines
        );
        usort(
            $maps,
            function ($map1, $map2) {
                return $map2['source'] - $map1['source'];
            }
        );

        return array_combine(array_column($maps, 'source'), $maps);
    },
    $blocks
);

//var_export($blocks);

foreach ($blocks as $block) {
    debugLevel(count($block));
}

$min = PHP_INT_MAX;

foreach ($seeds as $seedRange) {
    [$startSeed, $rangeLength, $endSeed] = $seedRange;
    echo ("$startSeed + $rangeLength  = $endSeed");
    for ($seed = $endSeed; $seed >= $startSeed;) {
        [$result, $destDiff, $pin] = rollDown($seed, $blocks, 0);
        $diff = min($seed - $startSeed, $destDiff);
        $min = min($min, $result - $diff);
        debugLevel("seed $seed = $result ; diff $destDiff <> $diff");
        $seed -= $diff ?: 1;
    }
}

var_export($min);

/**
 * @param int $number
 * @param array<int, array{dest: int, source: int, range: int, maxDest: int, maxSource: int}> $pins
 * @param int $index
 *
 * @return array
 */
function rollDown(int $number, array &$pins, int $index = 0): array
{
    global $names;

    /** @var array<int, array{dest: int, source: int, range: int, maxDest: int, maxSource: int}> $prevPin */
    debugLevel("Rolling down $names[$index] number $number", $index);

    // new pin range
    /** @var array{dest: int, source: int, range: int, maxDest: int, maxSource: int} $pin */
    $thisLevelPins = $pins[$index];
    /** @var array{dest: int, source: int, range: int, maxDest: int, maxSource: int} $pin */
    // matching pin for requested number
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
    $pin = reset($pin);

    // if no mapping - dest is the same is number
    if ($pin) {
        $dest = (($number - $pin['source']) + $pin['dest']);
        $minSource = $pin['source'];
        debugLevel("$number : found mapping: dest $dest > $minSource" . json_encode($pin), $index);
    } else {
        $dest = $number;
        $closetPin = array_column($thisLevelPins, 'maxSource');
        $closetPin = array_filter(
            $closetPin,
            function ($maxSource) use ($number) {
                return $maxSource < $number;
            }
        );
        rsort($closetPin);
        $closetPin = reset($closetPin);
        $minSource = $closetPin ? ($closetPin + 1) : 0;
        debugLevel("no mapping: $dest > $minSource ($closetPin)", $index);
    }

    $sourceDiff = $number - $minSource;

    // reached the bottom
    if (!isset($pins[$index + 1])) {
        debugLevel('Reached the bottom', $index);

        // function result & diff between requested number and min from range.
        return [$dest, $sourceDiff, $pin];
    }

    [$result, $destDiff, $destPin] = rollDown($dest, $pins, ++$index);

    $possibleDiff = min($sourceDiff, $destDiff);
    debugLevel("d$destDiff <> s$sourceDiff = $possibleDiff", $index - 1);

    return [$result, $possibleDiff, $pin];
}
