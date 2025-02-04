<?php
declare(strict_types=1);

namespace Day16_01;

use Exception;

function human_bytes(int $bytes): string {
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
            fn($line) => str_split($line),
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

class Redirect
{
    public const RIGHT_DOWN = [1, 1];
    public const LEFT_UP = [-1, -1];
}

class Head
{
    /**
     * @param array|int[] $position
     * @param array|int[] $direction
     */
    public function __construct(
        public readonly array $position = [0, 0],
        public readonly array $direction = Direction::RIGHT
    ) {
    }

    public function __toString(): string
    {
        return implode(',', $this->position) . '|' . implode(',', $this->direction);
    }
}

class Calculator
{
    public static array $instructionToDirections = [
        '|' => [Direction::UP, Direction::DOWN],
        '-' => [Direction::LEFT, Direction::RIGHT],
        '\\' => [Redirect::RIGHT_DOWN],
        '/' => [Redirect::LEFT_UP],
    ];

    public array $directionToArrow = [];

    private array $headHash = [];

    private array $posHash = [];

    private array $heads = [];

    private int $maxCol;

    private int $maxRow;

    private \SplFixedArray $matrix;

    /**
     * @var array|string[]
     */
    private array $field = [];

    public function __construct(array $matrix, private readonly bool $isDebug = false)
    {
        // transpose
        $matrix = array_map(null, ... $matrix);

        $this->maxCol = count($matrix[0]) - 1;
        $this->maxRow = count($matrix) - 1;

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

        $this->matrix = \SplFixedArray::fromArray($matrix);
    }

    /**
     * @param Head $head
     *
     * @return int
     */
    public function calculate(Head $head)
    {
        $this->heads = [$head];
        $this->headHash = [(string)$head => (string)$head];
        $this->posHash = [implode(',', $head->position) => $head->position];

        if ($this->isDebug) {
            $field = $this->field;
            $field[$head->position[0]][$head->position[1]] = $this->directionToArrow[implode(',', $head->direction)];
        }

        while ($this->heads) {
            $heads = [];
            foreach ($this->heads as $head) {
                $instruction = $this->matrix[$head->position[0]][$head->position[1]];

                $directions = self::$instructionToDirections[$instruction] ?? null;

                if ($directions) {
                    foreach ($directions as $direction) {
                        $newHead = $this->getNextHeadFromHeadAndDirection($head, $direction);
                        if ($newHead) {
                            $heads[] = $newHead;
                        }
                    }
                } else {
                    $newHead = $this->getNextHeadFromHeadAndDirection($head, $head->direction);
                    if ($newHead) {
                        $heads[] = $newHead;
                    }
                }
            }

            $this->heads = $heads;

            if ($this->isDebug) {
                foreach ($heads as $head) {
                    $field[$head->position[1]][$head->position[0]] = /*$directions
                        ? $instruction
                        : */
                        (is_numeric($field[$head->position[1]][$head->position[0]])
                            ? $field[$head->position[1]][$head->position[0]]++
                            : $this->directionToArrow[implode(',', $head->direction)]);
                }

                $this->debug(implode(PHP_EOL, $field) . PHP_EOL);
            }
        }

        return count($this->posHash);
    }

    public function getNextHeadFromHeadAndDirection(Head $head, array $redirect): ?Head
    {
        $direction = self::vmult($head->direction, $redirect);

        if (array_sum($direction) === 0) {
            $direction = $head->direction;
        }

        $newPosition = self::vadd($head->position, $direction);

        if (
            $newPosition[0] < 0 || $newPosition[1] < 0
            || $newPosition[0] > $this->maxCol || $newPosition[1] > $this->maxRow
        ) {
            return null;
        }

        $newHead = new Head($newPosition, $direction);

        $hash = (string)$newHead;
        if (!isset($this->headHash[$hash])) {
            $this->headHash[$hash] = $hash;
            $this->posHash[implode(',', $newPosition)] = $newPosition;
        } else {
            $newHead = null;
        }

        return $newHead;
    }

    public function debug($message)
    {
        if (!$this->isDebug) {
            return;
        }

        echo $message . PHP_EOL;
    }

    public static function vmult(array $v1, array $v2)
    {
        return [
            $v1[1] * $v2[0],
            $v1[0] * $v2[1],
        ];
    }

    public static function vadd(array $v1, array $v2)
    {
        return [
            $v1[0] + $v2[0],
            $v1[1] + $v2[1],
        ];
    }
}


$isDebug = false;

$start = microtime(true);
$mem = memory_get_usage();

//var_export(Calculator::vmult(Direction::RIGHT, Direction::RIGHT));
//var_export(Calculator::vmult(Direction::LEFT, Direction::RIGHT));
//var_export(Calculator::vmult(Direction::DOWN, Direction::RIGHT));
//var_export(Calculator::vmult(Direction::UP, Direction::RIGHT));
//exit;

$input = Reader::getInput('day_16.txt');

/** @var string[] $lines */
$matrix = Parser::parseInput($input, $isDebug);

$calculator = new Calculator($matrix, $isDebug);

$head = new Head();
echo $calculator->calculate($head) . PHP_EOL;

$result = [];

$size = count($matrix);

/**
 * todo: to make the calculation faster, store beam chains between calculations, and if a stored Head is found, don't
 * cast the beam, and re-use the chain instead
 */
for ($pos = 0; $pos < $size; $pos++) {
    $head = new Head([$pos, 0], Direction::DOWN);
    $result[] = $calculator->calculate($head);

    $head = new Head([$pos, $size - 1], Direction::UP);
    $result[] = $calculator->calculate($head);

    $head = new Head([0, $pos], Direction::RIGHT);
    $result[] = $calculator->calculate($head);

    $head = new Head([$size - 1, $pos], Direction::LEFT);
    $result[] = $calculator->calculate($head);
}

echo max($result) . PHP_EOL;

//
//echo chr(27) . chr(91) . 'H' . chr(27) . chr(91) . 'J';   //^[H^[J
//

$end = microtime(true);

echo PHP_EOL . 'Memory: ' . human_bytes(memory_get_usage() - $mem) . PHP_EOL;
printf("Exec time: %.4f\n", $end - $start);

