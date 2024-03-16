<?php

namespace App\Service\Results;

use ogrrd\CsvIterator\CsvIterator;

class NationalLotteryLottoResults extends AbstractLotteryResults
{
    const DRAW_NAME = 'national-lottery-lotto';
    const DRAW_RESULTS = 'https://www.national-lottery.co.uk/results/lotto/draw-history/csv';

    public function getDraws(): CsvIterator
    {
        if (!file_exists($this->results)) {
            $this->downloadResults();
        }

        $draws = new CsvIterator($this->results, ',');
        $draws->useFirstRowAsHeader();

        return $draws;
    }

    public function checkResults(array $ticket): array
    {
        $draws = $this->getDraws();
        $results = [];

        foreach ($draws as $draw) {
            $matches = 0;
            $bonusMatch = false;

            if (in_array($draw['Ball 1'], $ticket)) {
                $matches++;
            }

            if (in_array($draw['Ball 2'], $ticket)) {
                $matches++;
            }

            if (in_array($draw['Ball 3'], $ticket)) {
                $matches++;
            }

            if (in_array($draw['Ball 4'], $ticket)) {
                $matches++;
            }

            if (in_array($draw['Ball 5'], $ticket)) {
                $matches++;
            }

            if (in_array($draw['Ball 6'], $ticket)) {
                $matches++;
            }

            if ($matches === 5) {
                $bonusMatch = true;
            }

            $results[$draw['DrawDate']] = [$matches, $bonusMatch];
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function prizeValue($ballMatches, $bonus): int
    {
        switch ($ballMatches) {
            case 6:
                return 15_000_000;
            case 5:
                if ($bonus) {
                    return 1_000_000;
                }

                return 1_750;
            case 4:
                return 140;
            case 3:
                return 30;
            case 2:
                // Free ticket, return cost of entry
                return self::ticketCost();
        }

        return 0;
    }

    public function ticketCost(): int
    {
        return 2;
    }
}
