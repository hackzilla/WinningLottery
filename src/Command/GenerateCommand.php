<?php

namespace App\Command;

use App\Service\LotteryNumbers;
use App\Service\NationalLotteryResults;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate',
    description: 'Add a short description for your command',
)]
class GenerateCommand extends Command
{
    // Define constants for the order options
    public const ORDER_NONE = 'none';
    public const ORDER_HIGH_LOW = 'high-low';
    public const ORDER_LOW_HIGH = 'low-high';
    public const ORDER_LEAST_PICKED = 'least-picked';
    public const ORDER_MOST_PICKED = 'most-picked';
    public const ORDER_RANDOM = 'random';

    // An array of all valid order options
    public const VALID_ORDERS = [
        self::ORDER_NONE,
        self::ORDER_HIGH_LOW,
        self::ORDER_LOW_HIGH,
        self::ORDER_LEAST_PICKED,
        self::ORDER_MOST_PICKED,
        self::ORDER_RANDOM,
    ];

    public function __construct(
        private readonly NationalLotteryResults $lotteryResults,
        private readonly LotteryNumbers $lotteryNumbers,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('balls', 'b', InputOption::VALUE_REQUIRED, 'Total number of balls in the draw', 59)
            ->addOption(
                'order',
                'o',
                InputOption::VALUE_REQUIRED,
                'Order of balls in the draw',
                self::ORDER_NONE
            )
            ->addOption('result', 'r', InputOption::VALUE_NONE, 'Check tickets against the last 6 months of lottery draws')
            ->addOption('summary', 's', InputOption::VALUE_NONE, 'Summary of result of tickets against the last 6 months of lottery draws')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $totalBalls = (int) $input->getOption('balls');
        $order = $input->getOption('order');

        if (!in_array($order, self::VALID_ORDERS)) {
            $io->error(sprintf(
                "Invalid order option. Valid options are: %s.",
                implode(', ', self::VALID_ORDERS)
            ));
            return Command::FAILURE;
        }

        $tickets = $this->lotteryNumbers->generate($totalBalls, $order);

        $io->section('Tickets');
        $io->table([], $tickets);

        $io->info(sprintf('Total tickets: %d', count($tickets)));

        if ($input->getOption('result') || $input->getOption('summary')) {
            $io->section('Results');
            $datedResults = [];
            $prizes = [];
            $totalWinnings = 0;
            $totalCost = 0;

            foreach ($tickets as $ticket) {
                $results = $this->lotteryResults->checkResults($ticket);
                foreach ($results as $date => $data) {
                    $totalCost += 2;
                    $time = (new \DateTime($date . ' 00:00'))->format('U');

                    if ($data[0] < 2) {
                        continue;
                    }

                    $datedResults[$time][] = [$date, $data[0], $data[1] ? 'Yes': 'No'];

                    if (!isset($prizes[$time])) {
                        $prizes[$time] = [$date, 0];
                    }

                    $winnings = $this->prizeValue($data[0], $data[1]);
                    $totalWinnings += $winnings;
                    $prizes[$time][1] += $winnings;
                }
            }


            if ($input->getOption('result')) {
                ksort($datedResults);
                $headers = ['Date', 'Matches', 'Bonus'];
                foreach ($datedResults as $tableRows) {
                    $io->table($headers, $tableRows);
                }
            }

            if ($input->getOption('summary')) {
                ksort($prizes);
                $io->table(['Date', 'Winnings'], $prizes);
                $io->info(sprintf("Winnings: %d\nCost: %d\nProfit: %d", $totalWinnings, $totalCost, $totalWinnings - $totalCost));
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Approximate prize values
     */
    private function prizeValue($ballMatches, $bonus): int
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
                return 2;
        }

        return 0;
    }
}
