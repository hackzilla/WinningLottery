<?php

namespace App\Service;

use App\Command\GenerateCommand;
use App\Service\Results\NationalLotteryLottoResults;

class LotteryNumbers
{
    public function __construct(
        private readonly NationalLotteryLottoResults $lotteryResults,
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

        // These functions are only for 59 balls
        $functions = ['B', 'C', 'E', 'E', 'E'];
        $tickets = [];

        $i = 0;
        foreach ($functions as $func) {
            switch ($func) {
                case 'B':
                    $numBalls = 8;
                    break;
                case 'C':
                    $numBalls = 9;
                    break;
                case 'E':
                    $numBalls = 14;
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

    // Requires 8 numbers
    private function B($numbers): array
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
    private function C($numbers): array
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

    // Requires 14 numbers
    private function E($numbers): array
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
}
