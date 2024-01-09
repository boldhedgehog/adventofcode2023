<?php

namespace Day07_01;

$input = trim(\file_get_contents(__DIR__ . '/../in/day_07.txt'));

$translate = ['T' => 'a', 'J' => 'b', 'Q' => 'c', 'K' => 'd', 'A' => 'e'];
$input = strtr($input, $translate);

$labels = array_merge(
    range(2, 9),
    array_values($translate)
);

$labelValues = array_flip($labels);

//var_export($labelValues);

$fives = [];

foreach (array_keys($labelValues) as $label) {
    $hand = str_repeat($label, 5);
    $fives[$hand] = $hand;
}

$fours = [];

foreach ($labels as $label) {
    $card = str_repeat($label, 4);
    foreach ($labels as $endLabel) {
        if ($endLabel == $label) {
            continue;
        }

        $sorted = sortString($card . $endLabel);
        $fours[$sorted] = $sorted;
    }
}

$threes = [];

foreach ($labels as $label) {
    $card = str_repeat($label, 3);
    foreach ($labels as $nextLabel) {
        if ($nextLabel == $label) {
            continue;
        }

        foreach ($labels as $endLabel) {
            if ($nextLabel == $endLabel || $endLabel == $label) {
                continue;
            }

            $hand = sortString($card . $nextLabel . $endLabel);
            $threes[$hand] = $hand;
        }
    }
}

$pairs = [];

foreach ($labels as $label) {
    $card = str_repeat($label, 2);
    foreach ($labels as $nextLabel) {
        if ($nextLabel == $label) {
            continue;
        }

        foreach ($labels as $anotherNextLabel) {
            if ($anotherNextLabel == $label || $anotherNextLabel == $nextLabel) {
                continue;
            }

            foreach ($labels as $endLabel) {
                if ($nextLabel == $endLabel || $endLabel == $label || $endLabel == $anotherNextLabel) {
                    continue;
                }

                $hand = sortString($card . $nextLabel . $anotherNextLabel . $endLabel);
                $pairs[$hand] = $hand;
            }
        }
    }
}

$twoPairs = [];

foreach ($labels as $label) {
    $card = str_repeat($label, 2);
    foreach ($labels as $nextLabel) {
        if ($nextLabel == $label) {
            continue;
        }

        foreach ($labels as $endLabel) {
            if ($nextLabel == $endLabel || $endLabel == $label) {
                continue;
            }

            $hand = sortString($card . str_repeat($nextLabel, 2) . $endLabel);
            $twoPairs[$hand] = $hand;
        }
    }
}

$fullHouses = [];

foreach ($labels as $label) {
    $card = str_repeat($label, 3);
    foreach ($labels as $nextLabel) {
        if ($nextLabel == $label) {
            continue;
        }

        $hand = sortString($card . str_repeat($nextLabel, 2));
        $fullHouses[$hand] = $hand;
    }
}

//var_export($fives);
//var_export($fours);
//var_export($threes);
//var_export($pairs);
//var_export($twoPairs);
//var_export($fullHouses);
global $weights;

$weights = [
    $pairs,
    $twoPairs,
    $threes,
    $fullHouses,
    $fours,
    $fives
];

$input = array_map(
    function ($line) use ($labelValues) {
        [$hand, $bid] = explode(' ', $line);

        $numeric = array_map(
            function ($label) use ($labelValues) {
                return $labelValues[$label];
            },
            str_split($hand)
        );

        $sorted = sortString($hand);

        return [
            'hand' => $hand,
            'bid' => $bid,
            'numeric' => $numeric,
            'weight' => handWeight($sorted),
            'sorted' => $sorted
        ];
    },
    explode(PHP_EOL, $input)
);

var_export($input) . PHP_EOL;

usort(
    $input,
    function ($hand1, $hand2) {
        if ($hand1['weight'] == $hand2['weight']) {
            // need to compare each card
            foreach ($hand1['numeric'] as $key => $value) {
                if ($value != $hand2['numeric'][$key]) {
                    return $value - $hand2['numeric'][$key];
                }
            }
        } else {
            return $hand1['weight'] - $hand2['weight'];
        }

        return 0;
    }
);

$input = array_values($input);

var_export($input) . PHP_EOL;

$bids = [];

foreach ($input as $index => $hand) {
    $bids[] = ($index + 1) * $hand['bid'];
}

//var_export($bids);

var_export(array_sum($bids));

function handWeight(string $hand): int
{
    global $weights;

    $sorted = sortString($hand);

    foreach ($weights as $weight => $hands) {
        if (isset($hands[$hand])) {
            return $weight + 1;
        }
    }

    return 0;
}

function sortString(string $string): string
{
    $array = str_split($string);
    sort($array);

    return implode('', $array);
}
