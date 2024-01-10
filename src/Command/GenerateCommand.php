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
    public function __construct(
        private readonly NationalLotteryResults $lotteryResults,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('balls', 'b', InputOption::VALUE_REQUIRED, 'Total number of balls in the draw')
            ->addOption('shuffle', null, InputOption::VALUE_NONE, 'Randomise ball order')
            ->addOption('result', 'r', InputOption::VALUE_NONE, 'Check tickets against the last 6 months of lottery draws')
            ->addOption('summary', 's', InputOption::VALUE_NONE, 'Summary of result of tickets against the last 6 months of lottery draws')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $totalBalls = (int) $input->getOption('balls');
        $shuffle = $input->getOption('shuffle');
        $lotteryService = new LotteryNumbers();

        $tickets = $lotteryService->generate($totalBalls, $shuffle);

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
