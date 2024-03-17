<?php

declare(strict_types=1);

use App\Command\GenerateCommand;
use App\Service\LotteryNumbers;
use App\Service\Results\TestLottoResults;
use PHPUnit\Framework\TestCase;

class FunctionListTest extends TestCase
{
    public function testFunctionList(): void
    {
        $lotteryNumbers = $this->getLotteryNumbersService();

        for ($i = 32; $i < 71; $i++) {
            $functions = $lotteryNumbers->buildFunctionList($i);

            $this->assertSame(
                $i,
                $this->calculate($functions),
                'Functions ('.implode(',', $functions).') failed to add up to '.$i,
            );
        }
    }

    private function calculate(array $functions): int
    {
        $total = 0;

        foreach ($functions as $function) {
            $total += match ($function) {
                'A' => 6,
                'B' => 8,
                'C' => 9,
                'D' => 10,
                'E' => 14,
                'F' => 12,
                'G' => 13,
            };
        }

        return $total;
    }

    private function getLotteryNumbersService(): LotteryNumbers
    {
        return new LotteryNumbers(new TestLottoResults());
    }
}
