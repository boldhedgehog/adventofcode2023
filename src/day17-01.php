<?php
declare(strict_types=1);

namespace Day17_01;

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
     * @return array{int, array<int, array<int, string[]>>}
     */
    public static function parseInput(string $input, bool $isDebug = false): array
    {
        return array_map(
            fn($line) => array_map('intval', str_split($line)),
            explode(PHP_EOL, trim($input))
        );
    }
}

class Direction
{
    public const UP = [0, -1];

    public const DOWN = [0, 1];

    public const RIGHT = [1, 0];

    public const LEFT = [-1, 0];
}

class Crucible
{
    public static int $minSteps = 1;

    public static int $maxSteps = 3;

    private static int $maxPos = 1;

    public readonly string $index;

    public readonly string $posIndex;

    public int $heatLoss = 0;

    public static array $arrows = [];

    /**
     * @param array|int[] $position
     * @param array|int[] $direction
     */
    public function __construct(
        public readonly array $position = [0, 0],
        public readonly array $direction = Direction::RIGHT,
        public readonly int $steps = 1,
        public readonly ?Crucible $prev = null
    ) {
        if (!self::$arrows) {
            self::$arrows = [
                implode(',', Direction::UP) => '^',
                implode(',', Direction::DOWN) => 'V',
                implode(',', Direction::LEFT) => '<',
                implode(',', Direction::RIGHT) => '>',
            ];
        }

        $vertical = $direction[1] & 1;

        $this->posIndex = "{$position[0]},{$this->position[1]}";
        $this->index = "{$this->posIndex}|{$vertical}|{$steps}";
    }

    public function copy(array $direction = null): Crucible
    {
        return new Crucible($this->position, $direction ?: $this->direction, $this->steps, $this->prev);
    }

    public function setMaxPos(int $pos): void
    {
        self::$maxPos = $pos;
    }

    public function moveForward(): ?Crucible
    {
        $nextPos = Crucible::vadd($this->position, $this->direction);

        if (!$this->validatePosition($nextPos)) {
            return null;
        }

        $steps = $this->steps + 1;

        if ($steps > static::$maxSteps) {
            return null;
        }

        return new Crucible($nextPos, $this->direction, $steps, $this);
    }

    public static function vadd(array $v1, array $v2)
    {
        return [
            $v1[0] + $v2[0],
            $v1[1] + $v2[1],
        ];
    }

    public function validatePosition(array $position): bool
    {
        return $position[0] >= 0 && $position[1] >= 0
            && $position[0] < self::$maxPos && $position[1] < self::$maxPos;
    }

    public function moveRight(): ?Crucible
    {
        if ($this->steps < static::$minSteps) {
            return null;
        }

        // rotate clockwise
        $newDirection = [$this->direction[1], -$this->direction[0]];

        return $this->move($newDirection);
    }

    /**
     * @param array $newDirection
     *
     * @return Crucible|null
     */
    protected function move(array $newDirection): ?Crucible
    {
        $nextPos = Crucible::vadd($this->position, $newDirection);

        if (!$this->validatePosition($nextPos)) {
            return null;
        }

        return new Crucible($nextPos, $newDirection, prev: $this);
    }

    public function moveLeft(): ?Crucible
    {
        if ($this->steps < static::$minSteps) {
            return null;
        }

        // rotate counterclockwise
        $newDirection = [-$this->direction[1], $this->direction[0]];

        return $this->move($newDirection);
    }

    public function __toString(): string
    {
        return $this->posIndex . '|' . self::$arrows[implode(',', $this->direction)];
    }

    public function distanceTo(Crucible $crucible): int
    {
        return abs($crucible->position[0] - $this->position[0])
            + abs($crucible->position[1] - $this->position[1]);
    }
}

class Queue extends \SplPriorityQueue
{
    public function compare(mixed $priority1, mixed $priority2): int
    {
        return parent::compare($priority1, $priority2) * -1;
    }
}

class Calculator
{
    /**
     * @var string[]
     */
    private array $directionToArrow;

    /**
     * @var array|string[]
     */
    private array $field = [];

    private \SplFixedArray $matrix;

    private readonly Crucible $goal;

    private bool $isDistanceCalculated = false;

    private int $size;

    public function __construct(array $matrix, private readonly bool $isDebug = false)
    {
        if ($this->isDebug) {
            $this->directionToArrow = [
                implode(',', Direction::UP) => '^',
                implode(',', Direction::DOWN) => 'V',
                implode(',', Direction::LEFT) => '<',
                implode(',', Direction::RIGHT) => '>',
            ];

            $this->field = array_map(
                fn($row) => implode('', $row),
                $matrix
            );
        }

        // transpose
        $matrix = array_map(null, ... $matrix);

        $this->matrix = \SplFixedArray::fromArray($matrix);

        $this->size= $size = count($matrix);

        $this->goal = new Crucible([$size - 1, $size - 1]);
        $this->goal->heatLoss = $this->matrix[$size - 1][$size - 1];
        $this->goal->setMaxPos($size);
    }

    /**
     * @return int
     */
    public function calculate(Crucible $start)
    {
        $frontier = new Queue();

        $frontier->setExtractFlags(\SplPriorityQueue::EXTR_DATA);

        $frontier->insert($start, 0);

        $path[$start->posIndex] = $start;
        $costSoFar[$start->index] = 0;

        $startDown = $start->copy(Direction::DOWN);
        $costSoFar[$startDown->index] = 0;
        $frontier->insert($startDown, 0);

        if ($this->isDebug) {
            $field = implode(PHP_EOL, $this->field);

            $this->showField($field);
        }

        $result = 0;

        while (!$frontier->isEmpty() && $frontier->valid()) {
            /** @var Crucible $current */
            $current = $frontier->extract();

            if ($current->position == $this->goal->position) {
                $result = $costSoFar[$current->index];
                break;
            }

            $blocks = $this->getBlocks($current);

            foreach ($blocks as $next) {
                $heatLoss = $this->crucibleCost($next);
                $next->heatLoss = $heatLoss;

                $cost = $costSoFar[$current->index] + $heatLoss;

                if (!isset($costSoFar[$next->index]) || $cost < $costSoFar[$next->index]) {
                    $costSoFar[$next->index] = $cost;
                    $priority = $cost + ($this->isDistanceCalculated ? $this->getDistanceTo($next) : 0);

                    $frontier->insert($next, $priority);

                    $path[$next->posIndex] = $next;

                    if ($this->isDebug) {
                        $index = $next->position[0] + ($this->size + 1) * $next->position[1];
                        $field[$index] =
                            $this->directionToArrow[implode(',', $next->direction)];
                        $this->showField($field);
                        //usleep(50_000);
                    }
                }
            }

            //$frontier->rewind();
        }

        if ($this->isDebug) {
            $field = implode(PHP_EOL, $this->field);
            $optimalPath = $this->buildOptimalPath($path);
            foreach ($optimalPath as $crucible) {
                $index = $crucible->position[0] + ($this->size + 1) * $crucible->position[1];
                $field[$index] =
                    $this->directionToArrow[implode(',', $crucible->direction)];
            }

            $this->showField($field);
        }

        return $result;
    }

    private function showField(string $field)
    {
        echo chr(27) . chr(91) . 'H' . chr(27) . chr(91) . 'J';   //^[H^[J

        //$text = implode(PHP_EOL, $field);
        $text = $field;

        $arrows = ['<', '>', 'V', '^'];

        $text = str_replace(
            $arrows,
            array_map(
                fn($char) => "\e[0;31m{$char}\e[0m",
                $arrows
            ),
            $text
        );

        echo PHP_EOL . $text . PHP_EOL;
    }

    /**
     * @param Crucible $crucible
     *
     * @return Crucible[]
     */
    private function getBlocks(Crucible $crucible): array
    {
        return array_filter(
            [
                $crucible->moveForward(),
                $crucible->moveLeft(),
                $crucible->moveRight(),
            ]
        );
    }

    private function crucibleCost(Crucible $crucible): int
    {
        return $this->matrix[$crucible->position[0]][$crucible->position[1]];
    }

    /**
     * @param Crucible $next
     *
     * @return int
     */
    protected function getDistanceTo(Crucible $next): int
    {
        return $next->distanceTo($this->goal);
    }

    /**
     * @param Crucible[] $nodes
     *
     * @return Crucible[]
     */
    private function buildOptimalPath(array $nodes): array
    {
        $goal = $nodes[$this->goal->posIndex];
        $path[] = $goal;
        $start = reset($nodes);

        $previousNode = $goal->prev;

        while ($previousNode && $previousNode->posIndex !== $start->posIndex) {
            $path[] = $previousNode;
            $previousNode = $previousNode->prev;
        }

        return $path;
    }

    public function setIsDistanceCalculated(bool $isDistanceCalculated): void
    {
        $this->isDistanceCalculated = $isDistanceCalculated;
    }
}

$isDebug = false;

$start = microtime(true);
$mem = memory_get_usage();

$input = Reader::getInput('day_17.txt');

/** @var string[] $lines */
$matrix = Parser::parseInput($input, $isDebug);

$calculator = new Calculator($matrix, $isDebug);

$calculator->setIsDistanceCalculated(true);

$crucible = new Crucible([0, 0]);

echo $calculator->calculate($crucible) . PHP_EOL;

$calculator = new Calculator($matrix, $isDebug);

$calculator->setIsDistanceCalculated(true);

$crucible = new Crucible([0, 0]);
$crucible::$minSteps = 4;
$crucible::$maxSteps = 10;

echo $calculator->calculate($crucible) . PHP_EOL;

$end = microtime(true);

echo PHP_EOL . 'Memory: ' . human_bytes(memory_get_usage() - $mem) . PHP_EOL;
printf("Exec time: %.4f\n", $end - $start);

// 817
// 925
