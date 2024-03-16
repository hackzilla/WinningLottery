<?php

namespace App\Service\Results;

use ogrrd\CsvIterator\CsvIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class AbstractLotteryResults
{
    protected const DRAW_NAME = '';
    protected const DRAW_RESULTS = '';
    protected readonly string $results;

    public function __construct(
        ParameterBagInterface $bag,
    )
    {
        if (static::DRAW_NAME === '') {
            throw new \Exception('Draw name not set in: ' . __CLASS__);
        }

        if (static::DRAW_RESULTS === '') {
            throw new \Exception('Draw results not set in: ' . __CLASS__);
        }

        $directory = $bag->get('kernel.project_dir').'/var/results/' . static::DRAW_NAME . '/';

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $this->results = $directory . date('Y-m-d') . '.csv';
    }

    abstract public function getDraws(): CsvIterator;

    abstract public function checkResults(array $ticket): array;

    /**
     * Approximate prize values
     */
    abstract public function prizeValue($ballMatches, $bonus): int;
    abstract public function ticketCost(): int;

    protected function downloadResults(): void
    {
        $content = file_get_contents(static::DRAW_RESULTS);

        if ($content === false) {
            throw new \Exception("Failed to download the file.");
        }

        $written = file_put_contents($this->results, $content);
        if ($written === false) {
            throw new \Exception("Failed to write the file to disk.");
        }
    }
}
