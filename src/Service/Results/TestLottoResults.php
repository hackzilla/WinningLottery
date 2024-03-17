<?php

namespace App\Service\Results;

use ogrrd\CsvIterator\CsvIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TestLottoResults extends AbstractLotteryResults
{
    const DRAW_NAME = 'test';
    const DRAW_RESULTS = 'https://localhost';

    public function __construct()
    {
    }

    public function getDraws(): CsvIterator
    {
        $draws = new CsvIterator('', ',');
        $draws->useFirstRowAsHeader();

        return $draws;
    }

    public function checkResults(array $ticket): array
    {
       return [];
    }

    /**
     * @inheritDoc
     */
    public function prizeValue($ballMatches, $bonus): int
    {
        return 0;
    }

    public function ticketCost(): int
    {
        return 1;
    }
}
