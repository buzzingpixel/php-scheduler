<?php

declare(strict_types=1);

namespace BuzzingPixel\Scheduler;

use function array_merge;
use function explode;
use function implode;

readonly class ScheduleItem
{
    /**
     * @param class-string $class
     * @param mixed[]      $context
     */
    public function __construct(
        public Frequency $runEvery,
        public string $class,
        public string $method = '__invoke',
        public array $context = [],
    ) {
    }

    public function key(): string
    {
        return implode('_', array_merge(
            ['schedule_tracking_class'],
            explode('\\', $this->class),
        ));
    }
}
