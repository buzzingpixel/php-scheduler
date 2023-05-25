<?php

declare(strict_types=1);

namespace BuzzingPixel\Scheduler\Framework;

use BuzzingPixel\Scheduler\ScheduleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunScheduleSymfonyCommand extends Command
{
    public function __construct(
        private readonly ScheduleHandler $scheduleHandler,
        public string|null $name = 'buzzingpixel-schedule:run',
    ) {
        parent::__construct($name);
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $this->scheduleHandler->runSchedule();

        return 0;
    }
}
