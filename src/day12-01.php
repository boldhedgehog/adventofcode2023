<?php

namespace Day12_01;

class Automata
{
    /**
     * @var State[]
     */
    public array $states = [];

    /**
     * @var int[]
     */
    public array $map = [];

    public bool $isDebug = false;

    public static function fromArray(array $map): Automata
    {
        $automata = new Automata();

        $automata->map = $map;

        $map = array_reverse($map);

        // total number of states: the sum of hashes + in-between dots + possible starting dots and finishing dot
        // for broken springs map 2,1,2,1,4 the regex is:
        // \.*##\+#\.+##\.+#\.+####\.*

        // reversed states. The last 2 steps are end steps
        /** @var State[] $states */
        $states = [];

        foreach ($map as $numBrokenSprings) {
            // all next dots state point to itself
            $dotLoopState = new State(hash: (end($states) ?: null), index: count($states));
            $dotLoopState->dot = $dotLoopState;
            $states[] = $dotLoopState;
            // first dot after the hashes
            if (count($states) > 1) {
                $states[] = new State(dot: $dotLoopState, index: count($states));
            }
            // generate next # states
            for ($next = (end($states) ?: null); $numBrokenSprings > 1; $numBrokenSprings--, $next = (end($states) ?: null)) {
                $states[] = new State(hash: $next, index: count($states));
            }
        }

        // first possible dot
        $dotLoopState = new State(hash: end($states), index: count($states));
        $dotLoopState->dot = $dotLoopState;
        $states[] = $dotLoopState;

        $automata->states = array_reverse($states);

        return $automata;
    }

    public function match(string $springs)
    {
        /** @var array<int, Head> $heads */
        $heads = [$this->states[0]->index => new Head($this->states[0], 1)];


        if ($this->isDebug) {
            $strLen = strlen((string)$this->states[0]) + 2;
            $thisLen = strlen((string)$this);
        }

        foreach (str_split($springs) as $spring) {
            /** @var array<int, Head> $newHeads */
            $newHeads = [];
            foreach ($heads as $head) {
                $state = $head->state;
                if (($spring == '.' || $spring == '?') && $state->dot) {
                    $nextState = $state->dot;
                    if (!isset($newHeads[$nextState->index])) {
                        $newHeads[$nextState->index] = new Head($nextState, $head->hits);
                    } else {
                        $newHeads[$nextState->index]->hits += $head->hits;
                    }
                }
                if (($spring == '#' || $spring == '?') && $state->hash) {
                    $nextState = $state->hash;
                    if (!isset($newHeads[$nextState->index])) {
                        $newHeads[$nextState->index] = new Head($nextState, $head->hits);
                    } else {
                        $newHeads[$nextState->index]->hits += $head->hits;
                    }
                }
            }
            $heads = $newHeads;

            if ($this->isDebug) {
                echo str_repeat('-', $thisLen) . PHP_EOL;
                echo $this . PHP_EOL;

                foreach ($heads as $head) {
                    $strIndex = (count($this->states) - $head->state->index - 1) * $strLen;
                    $headsString = str_repeat(' ', $strIndex) . "{$head->state->index}/$head->hits";
                    echo $headsString . PHP_EOL;
                }
            }
        }

        return end($heads)->hits;
    }

    public function __toString(): string
    {
        $string = [];
        foreach ($this->states as $state) {
            $string[] = (string)$state;
        }

        return implode('->', $string);
    }
}

class Head
{
    public function __construct(public ?State $state = null, public int $hits = 0)
    {
    }
}

class State
{
    public int $hits = 0;

    public function __construct(
        public ?State $dot = null,
        public ?State $hash = null,
        public int $index = 0
    ) {
    }

    public function __toString()
    {
        $hash = $this->hash ? sprintf("#%02d", $this->hash->index) : '   ';
        $dot = $this->dot ? sprintf(".%02d", $this->dot->index) : '   ';
        $dot = $this->dot === $this ? '.<<' : $dot;
        $index = str_pad($this->index, 2, '0', STR_PAD_LEFT);

        return "($index)[$dot$hash]";
    }
}

class Reader
{
    public static function getInput($fileName): string
    {
        $filepath = __DIR__ . "/../in/$fileName";
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
     * @return array<int, array{springs: string, map: int[]}>
     */
    public static function parseInput($input): array
    {
        $array = array_map(
            function ($line) {
                [$springs, $map] = explode(' ', $line);

                return [
                    'springs' => $springs,
                    'map' => array_map('intval', explode(',', $map)),
                ];
            },
            explode(PHP_EOL, $input)
        );

        return array_filter($array);
    }
}

class Parser2
{
    /**
     * @param array<int, array{springs: string, map: int[]}> $lines
     *
     * @return array<int, array{springs: string, map: int[]}>
     */
    public static function unfold(array $lines): array
    {
        return array_map(
            function (array $line) {
                /** @var array{springs: string, map: int[]} $line */
                $unfoldedSprings = implode(
                    '?',
                    array_fill(0, 5, $line['springs'])
                );
                $unfoldedMap = array_fill(0, 5, $line['map']);
                $unfoldedMap = array_merge(... $unfoldedMap);

                return ['springs' => $unfoldedSprings, 'map' => $unfoldedMap];

            }, $lines
        );
    }
}

class Calculator
{

    public function __construct(public bool $isDebug = false)
    {
    }

    public function calculate($lines)
    {
        $hits = 0;

        foreach ($lines as $line) {
            $automata = Automata::fromArray($line['map']);
            $automata->isDebug = $this->isDebug;

            if ($automata->isDebug) {
                echo implode(',', $automata->map) . PHP_EOL;
                echo $automata . PHP_EOL;
            }

            $hits += $automata->match($line['springs']);
        }

        return $hits;
    }
}

$lines = Parser::parseInput(Reader::getInput('day_12.txt'));

$calculator = new Calculator();

echo $calculator->calculate($lines) . PHP_EOL;

$lines = Parser2::unfold($lines);

$calculator = new Calculator();

echo $calculator->calculate($lines) . PHP_EOL;
