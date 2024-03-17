<?php

declare(strict_types=1);

use App\Command\GenerateCommand;
use App\Service\LotteryNumbers;
use App\Service\Results\TestLottoResults;
use PHPUnit\Framework\TestCase;

class MinimalLotteryDesignTest extends TestCase
{
    public function testA(): void
    {
        $lotteryNumbers = $this->getLotteryNumbersService();
        $tickets = $lotteryNumbers->generate(6, GenerateCommand::ORDER_NONE);
        $this->assertSame([
            [1,2,3,4,5,6],
        ], $tickets);
    }

    public function testB(): void
    {
        $lotteryNumbers = $this->getLotteryNumbersService();
        $tickets = $lotteryNumbers->generate(8, GenerateCommand::ORDER_NONE);
        $this->assertSame([
            [1,2,3,4,5,6],
            [1,2,3,4,7,8],
            [1,2,5,6,7,8],
        ], $tickets);
    }

    public function testC(): void
    {
        $lotteryNumbers = $this->getLotteryNumbersService();
        $tickets = $lotteryNumbers->generate(9, GenerateCommand::ORDER_NONE);
        $this->assertSame([
            [1,2,3,4,5,6],
            [1,2,3,7,8,9],
            [4,5,6,7,8,9],
        ], $tickets);
    }

    public function testD(): void
    {
        $lotteryNumbers = $this->getLotteryNumbersService();
        $tickets = $lotteryNumbers->generate(10, GenerateCommand::ORDER_NONE);
        $this->assertSame([
            [1,2,3,4,9,10],
            [1,2,5,6,9,10],
            [1,2,7,8,9,10],
            [3,4,5,6,7,8],
        ], $tickets);
    }

    public function testE(): void
    {
        $lotteryNumbers = $this->getLotteryNumbersService();
        $tickets = $lotteryNumbers->generate(14, GenerateCommand::ORDER_NONE);
        $this->assertSame([
            [1,2,3,4,9,10],
            [1,2,5,6,13,14],
            [1,2,7,8,11,12],
            [3,4,5,6,11,12],
            [3,4,7,8,13,14],
            [5,6,7,8,9,10],
            [9,10,11,12,13,14],
        ], $tickets);
    }

    public function testF(): void
    {
        $lotteryNumbers = $this->getLotteryNumbersService();
        $tickets = $lotteryNumbers->generate(12, GenerateCommand::ORDER_NONE);
        $this->assertSame([
            [1,2,3,4,5,6],
            [1,2,3,7,8,9],
            [1,2,3,10,11,12],
            [4,5,6,7,8,9],
            [4,5,6,10,11,12],
            [7,8,9,10,11,12],
        ], $tickets);
    }

    public function testG(): void
    {
        $lotteryNumbers = $this->getLotteryNumbersService();
        $tickets = $lotteryNumbers->generate(13, GenerateCommand::ORDER_NONE);
        $this->assertSame([
            [1,2,3,4,9,10],
            [1,2,5,6,9,13],
            [1,2,7,8,11,12],
            [3,4,5,6,11,12],
            [3,4,6,7,8,13],
            [5,6,7,8,9,10],
            [2,9,10,11,12,13],
        ], $tickets);
    }

    private function getLotteryNumbersService(): LotteryNumbers
    {

        return new LotteryNumbers(new TestLottoResults());
    }
}
