<?php

namespace App\Service\Results;

use ogrrd\CsvIterator\CsvIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class NationalLotteryResults extends AbstractLotteryResults
{
    const DRAW_NAME = 'lotto';
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

    private function downloadResults(): void
    {
        $content = file_get_contents(self::DRAW_RESULTS);
        if ($content === false) {
            throw new \Exception("Failed to download the file.");
        }

        $written = file_put_contents($this->results, $content);
        if ($written === false) {
            throw new \Exception("Failed to write the file to disk.");
        }
    }
}
