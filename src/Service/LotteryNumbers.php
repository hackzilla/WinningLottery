<?php

namespace App\Service;

class LotteryNumbers
{
    public function generate(int $totalBalls, bool $shuffle): array
    {
        $balls = range(1, $totalBalls);

        if ($shuffle) {
            shuffle($balls);
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
