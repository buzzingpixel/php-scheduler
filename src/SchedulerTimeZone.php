<?php

declare(strict_types=1);

namespace BuzzingPixel\Scheduler;

use DateTimeZone;

readonly class SchedulerTimeZone
{
    public function __construct(
        public DateTimeZone $timeZone = new DateTimeZone('UTC'),
    ) {
    }
}
