<?php

declare(strict_types=1);

namespace BuzzingPixel\Scheduler;

interface ScheduleFactory
{
    public function createSchedule(): ScheduleItemCollection;
}
