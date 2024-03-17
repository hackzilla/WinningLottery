<?php

namespace App\Service;

use App\Command\GenerateCommand;
use App\Service\Results\AbstractLotteryResults;
use App\Service\Results\NationalLotteryLottoResults;

class LotteryNumbers
{
    public function __construct(
        private readonly AbstractLotteryResults $lotteryResults,
    )
    {
    }

    public function generate(int $totalBalls, string $order): array
    {
        $balls = range(1, $totalBalls);

        switch ($order) {
            case GenerateCommand::ORDER_HIGH_LOW:
                rsort($balls); // Sorts the balls from highest to lowest
                break;
            case GenerateCommand::ORDER_LOW_HIGH:
                sort($balls); // Sorts the balls from lowest to highest
                break;
            case GenerateCommand::ORDER_LEAST_PICKED:
            case GenerateCommand::ORDER_MOST_PICKED:
                $draws = $this->lotteryResults->getDraws();
                $ballCounts = [];

                foreach ($draws as $draw) {
                    // Iterate over each ball in the draw
                    for ($i = 1; $i <= 6; $i++) {
                        $ballKey = "Ball $i";
                        $ballValue = $draw[$ballKey];

                        // Increment the count for the ball value
                        if (!isset($ballCounts[$ballValue])) {
                            $ballCounts[$ballValue] = 0;
                        }

                        $ballCounts[$ballValue]++;
                    }
                }

                // Sort balls based on their counts
                usort($balls, function ($ballA, $ballB) use ($order, $ballCounts) {
                    $ballACount = $ballCounts[$ballA] ?? 0;
                    $ballBCount = $ballCounts[$ballB] ?? 0;

                    return ($order === GenerateCommand::ORDER_LEAST_PICKED)
                        ? $ballACount <=> $ballBCount
                        : $ballBCount <=> $ballACount;
                });

                break;
            case GenerateCommand::ORDER_RANDOM:
                shuffle($balls); // Shuffles the balls randomly
                break;
            case GenerateCommand::ORDER_NONE:
            default:
                // No specific ordering, keep the original order
                break;
        }

        $functions = $this->buildFunctionList($totalBalls);
        $tickets = [];

        $i = 0;
        foreach ($functions as $func) {
            switch ($func) {
                case 'A':
                    $numBalls = 6;
                    break;
                case 'B':
                    $numBalls = 8;
                    break;
                case 'C':
                    $numBalls = 9;
                    break;
                case 'D':
                    $numBalls = 10;
                    break;
                case 'E':
                    $numBalls = 14;
                    break;
                case 'F':
                    $numBalls = 12;
                    break;
                case 'G':
                    $numBalls = 13;
                    break;
                default:
                    // shouldn't get here with 59 balls
                    return $tickets;
            }

            $numbers = array_slice($balls, $i, $numBalls);
            $tickets = array_merge($tickets, $this->$func($numbers));

            $i += $numBalls;
        }

        return $tickets;
    }

    // Requires 6 numbers
    private function A(array $numbers): array
    {
        $row = [];

        for ($i = 0; $i < count($numbers); $i ++) {
            $row[] = $numbers[$i];
        }

        return [$row];
    }

    // Requires 8 numbers
    private function B(array $numbers): array
    {
        $pairs = [];

        for ($i = 0; $i < count($numbers); $i += 2) {
            $pairs[] = array_slice($numbers, $i, 2);
        }

        return [
            [...$pairs[0], ...$pairs[1], ...$pairs[2]],
            [...$pairs[0], ...$pairs[1], ...$pairs[3]],
            [...$pairs[0], ...$pairs[2], ...$pairs[3]],
        ];
    }

    // Requires 9 numbers
    private function C(array $numbers): array
    {
        $pairs = [];

        for ($i = 0; $i < count($numbers); $i += 3) {
            $pairs[] = array_slice($numbers, $i, 3);
        }

        return [
            [...$pairs[0], ...$pairs[1]],
            [...$pairs[0], ...$pairs[2]],
            [...$pairs[1], ...$pairs[2]],
        ];
    }

    // Requires 10 numbers
    private function D(array $numbers): array
    {
        $pairs = [];

        for ($i = 0; $i < count($numbers); $i += 2) {
            $pairs[] = array_slice($numbers, $i, 2);
        }

        return [
            [...$pairs[0], ...$pairs[1], ...$pairs[4]],
            [...$pairs[0], ...$pairs[2], ...$pairs[4]],
            [...$pairs[0], ...$pairs[3], ...$pairs[4]],
            [...$pairs[1], ...$pairs[2], ...$pairs[3]],
        ];
    }

    // Requires 14 numbers
    private function E(array $numbers): array
    {
        $pairs = [];

        for ($i = 0; $i < count($numbers); $i += 2) {
            $pairs[] = array_slice($numbers, $i, 2);
        }

        return [
            [...$pairs[0], ...$pairs[1], ...$pairs[4]],
            [...$pairs[0], ...$pairs[2], ...$pairs[6]],
            [...$pairs[0], ...$pairs[3], ...$pairs[5]],
            [...$pairs[1], ...$pairs[2], ...$pairs[5]],
            [...$pairs[1], ...$pairs[3], ...$pairs[6]],
            [...$pairs[2], ...$pairs[3], ...$pairs[4]],
            [...$pairs[4], ...$pairs[5], ...$pairs[6]],
        ];
    }

    // Requires 12 numbers
    private function F(array $numbers): array
    {
        $pairs = [];

        for ($i = 0; $i < count($numbers); $i += 3) {
            $pairs[] = array_slice($numbers, $i, 3);
        }

        return [
            [...$pairs[0], ...$pairs[1]],
            [...$pairs[0], ...$pairs[2]],
            [...$pairs[0], ...$pairs[3]],
            [...$pairs[1], ...$pairs[2]],
            [...$pairs[1], ...$pairs[3]],
            [...$pairs[2], ...$pairs[3]],
        ];
    }

    // Requires 13 numbers
    private function G(array $numbers): array
    {
        $pairs = [];

        for ($i = 0; $i < count($numbers); $i += 2) {
            $pairs[] = array_slice($numbers, $i, 2);
        }

        // Swap 14 for any other number

        return [
            [...$pairs[0], ...$pairs[1], ...$pairs[4]],
            [...$pairs[0], ...$pairs[2], $pairs[4][0], ...$pairs[6]],
            [...$pairs[0], ...$pairs[3], ...$pairs[5]],
            [...$pairs[1], ...$pairs[2], ...$pairs[5]],
            [...$pairs[1], $pairs[2][1], ...$pairs[3], ...$pairs[6]],
            [...$pairs[2], ...$pairs[3], ...$pairs[4]],
            [$pairs[0][1], ...$pairs[4], ...$pairs[5], ...$pairs[6]],
        ];
    }

    public function buildFunctionList(int $ballCount): array
    {
        switch ($ballCount)
        {
            case 6:
                return ['A'];
            case 8:
                return ['B'];
            case 9:
                return ['C'];
            case 10:
                return ['D'];
            case 14:
                return ['E'];
            case 12:
                return ['F'];
            case 13:
                return ['G'];
            case 31:
                return ['A', 'A', 'A', 'A', 'A'];
            case 32:
                return ['A', 'A', 'A', 'A', 'B'];
            case 33:
                return ['A', 'A', 'A', 'A', 'C'];
            case 34:
                return ['A', 'A', 'A', 'A', 'D'];
            case 35:
                return ['A', 'A', 'A', 'B', 'C'];
            case 36:
                return ['A', 'A', 'A', 'C', 'C'];
            case 37:
                return ['A', 'A', 'A', 'C', 'D'];
            case 38:
                return ['A', 'A', 'A', 'A', 'E'];
            case 39:
                return ['A', 'A', 'C', 'C', 'C'];
            case 40:
                return ['A', 'A', 'C', 'C', 'D'];
            case 41:
                return ['A', 'A', 'A', 'C', 'E'];
            case 42:
                return ['A', 'C', 'C', 'C', 'C'];
            case 43:
                return ['A', 'C', 'C', 'C', 'D'];
            case 44:
                return ['A', 'A', 'C', 'C', 'E'];
            case 45:
                return ['C', 'C', 'C', 'C', 'C'];
            case 46:
                return ['C', 'C', 'C', 'C', 'D'];
            case 47:
                return ['A', 'C', 'C', 'C', 'E'];
            case 48:
                return ['A', 'C', 'C', 'D', 'E'];
            case 49:
                return ['A', 'A', 'C', 'E', 'E'];
            case 50:
                return ['A', 'A', 'D', 'E', 'E'];
            case 51:
                return ['A', 'A', 'E', 'F', 'G'];
            case 52:
                return ['A', 'C', 'C', 'E', 'E'];
            case 53:
                return ['A', 'C', 'D', 'E', 'E'];
            case 54:
                return ['A', 'A', 'E', 'E', 'E'];
            case 55:
                return ['C', 'C', 'C', 'E', 'E'];
            case 56:
                return ['C', 'C', 'D', 'E', 'E'];
            case 57:
                return ['A', 'C', 'E', 'E', 'E'];
            case 58:
                return ['A', 'D', 'E', 'E', 'E'];
            case 59:
                return ['B', 'C', 'E', 'E', 'E'];
            case 60:
                return ['C', 'C', 'E', 'E', 'E'];
            case 61:
                return ['C', 'D', 'E', 'E', 'E'];
            case 62:
                return ['D', 'D', 'E', 'E', 'E'];
            case 63:
                return ['C', 'E', 'E', 'E', 'F'];
            case 64:
                return ['D', 'E', 'E', 'E', 'F'];
            case 65:
                return ['C', 'E', 'E', 'E', 'E'];
            case 66:
                return ['D', 'E', 'E', 'E', 'E'];
            case 67:
                return ['E', 'E', 'E', 'F', 'G'];
            case 68:
                return ['E', 'E', 'E', 'E', 'F'];
            case 69:
                return ['E', 'E', 'E', 'E', 'G'];
            case 70:
                return ['E', 'E', 'E', 'E', 'E'];
        }

        throw new \Exception('Unhandled balls');
    }
}
