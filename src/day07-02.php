<?php

namespace Day07_02;

$input = trim(\file_get_contents(__DIR__ . '/../in/day_07.txt'));

class HandsGenerator
{
    public static $names = [
        'highHand',
        'pairs',
        'twoPairs',
        'threes',
        'fullHouses',
        'fours',
        'fives',
    ];

    private array $fives = [];

    private array $fours = [];

    private array $threes = [];

    private array $pairs = [];

    private array $fullHouses = [];

    private array $twoPairs = [];

    private array $combinations = [];

    /**
     * @param array $labels
     *
     * @return array
     */
    public function generate(array $labels): array
    {
        $this->generateFives($labels);
        $this->generateFours($labels);
        $this->generateThrees($labels);
        $this->generatePairs($labels);
        $this->generateTwoPairs($labels);
        $this->generateFullHouses($labels);

        return $this->combinations = [
            $this->fives,
            $this->fours,
            $this->fullHouses,
            $this->threes,
            $this->twoPairs,
            $this->pairs,
        ];
    }

    private function generateFives($labels)
    {
        foreach ($labels as $label) {
            $hand = str_repeat($label, 5);
            $this->fives[$hand] = $hand;
        }
    }

    private function generateFours($labels)
    {
        foreach ($labels as $label) {
            $card = str_repeat($label, 4);
            foreach ($labels as $endLabel) {
                if ($endLabel == $label) {
                    continue;
                }

                $hand = $this->sortString($card . $endLabel);

                if ($endLabel == 1 || $label == 1) {
                    $this->fives[$hand] = $hand;
                }

                $this->fours[$hand] = $hand;
            }
        }
    }

    public static function sortString(string $string): string
    {
        $array = str_split($string);
        sort($array);

        return implode('', $array);
    }

    private function generateThrees($labels)
    {
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

                    $hand = $this->sortString($card . $nextLabel . $endLabel);

                    if ($nextLabel == 1 || $endLabel == 1 || $label == 1) {
                        $this->fours[$hand] = $hand;
                    }

                    $this->threes[$hand] = $hand;
                }
            }
        }
    }

    private function generatePairs($labels)
    {
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

                        $hand = $this->sortString($card . $nextLabel . $anotherNextLabel . $endLabel);

                        if ($nextLabel == 1 || $anotherNextLabel == 1 || $endLabel == 1) {
                            $this->threes[$hand] = $hand;
                        } elseif ($label == 1) {
                            $this->threes[$hand] = $hand;
                        }

                        $this->pairs[$hand] = $hand;
                    }
                }
            }
        }
    }

    private function generateTwoPairs($labels)
    {
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

                    $hand = $this->sortString($card . str_repeat($nextLabel, 2) . $endLabel);

                    if ($nextLabel == 1 || $label == 1) {
                        $this->fours[$hand] = $hand;
                    } elseif ($endLabel == 1) {
                        $this->fullHouses[$hand] = $hand;
                    }

                    $this->twoPairs[$hand] = $hand;
                }
            }
        }
    }

    private function generateFullHouses($labels)
    {
        foreach ($labels as $label) {
            $card = str_repeat($label, 3);
            foreach ($labels as $endLabel) {
                if ($endLabel == $label) {
                    continue;
                }

                $hand = $this->sortString($card . str_repeat($endLabel, 2));

                if ($endLabel == 1 || $label == 1) {
                    $this->fives[$hand] = $hand;
                }

                $this->fullHouses[$hand] = $hand;
            }
        }
    }

    public function handWeight(string $hand): int
    {
        $sorted = $this->sortString($hand);

        foreach ($this->combinations as $weight => $hands) {
            if (isset($hands[$sorted])) {
                return count($this->combinations) - $weight;
            }
        }

        return (int)str_contains($hand, 1);
    }
}

$translate = ['J' => 1, 'T' => 'a', 'Q' => 'c', 'K' => 'd', 'A' => 'e'];
$input = strtr($input, $translate);

$labels = array_merge(
    range(2, 9),
    array_values($translate)
);

sort($labels);

$labelValues = array_flip($labels);

$gen = new HandsGenerator();

$weights = $gen->generate($labels);

//var_export($weights);

$input = array_map(
    function ($line) use ($labelValues, $gen) {
        $translate = array_flip(['J' => 1, 'T' => 'a', 'Q' => 'c', 'K' => 'd', 'A' => 'e']);

        [$hand, $bid] = explode(' ', $line);

        $numeric = array_map(
            function ($label) use ($labelValues) {
                return $labelValues[$label];
            },
            str_split($hand)
        );

        $handWeight = $gen->handWeight($hand);

        return [
            'hand' => $hand,
            'original' => strtr($hand, $translate),
            'sorted' => HandsGenerator::sortString($hand),
            'bid' => (int)$bid,
            'numeric' => $numeric,
            'weight' => $handWeight,
            'type' => HandsGenerator::$names[$handWeight],
        ];
    },
    explode(PHP_EOL, $input)
);

//var_export($input) . PHP_EOL;

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

//var_export($input) . PHP_EOL;

$bids = [];

foreach ($input as $index => $hand) {
    $bids[] = ($index + 1) * $hand['bid'];
}

//foreach ($input as $index => $hand) {
//    $order = $index + 1;
//    $result = $order * $hand['bid'];
//    echo "$order => {$hand['original']} : {$hand['hand']} : {$hand['sorted']} : {$hand['weight']} : {$hand['type']} : {$hand['bid']} = $result" . PHP_EOL;
//    echo "{$hand['original']} {$hand['weight']}" . PHP_EOL;
//}

//var_export($bids);

var_export(array_sum($bids));
