<?php

declare(strict_types=1);

namespace BuzzingPixel\Scheduler\RedisDriver;

use BuzzingPixel\Scheduler\PersistentScheduleItem;
use BuzzingPixel\Scheduler\PersistentScheduleItemCollection;
use BuzzingPixel\Scheduler\ScheduleHandler;

readonly class RedisScheduleHandler implements ScheduleHandler
{
    public function __construct(
        private RunSchedule $runSchedule,
        private FetchSchedule $fetchSchedule,
        private PersistScheduleItem $persistScheduleItem,
        private int $runExpiresAfterSeconds = ScheduleHandler::RUN_EXPIRES_AFTER_SECONDS,
    ) {
    }

    public function runExpiresAfterSeconds(): int
    {
        return $this->runExpiresAfterSeconds;
    }

    public function runSchedule(): void
    {
        $this->runSchedule->run(
            $this->runExpiresAfterSeconds(),
        );
    }

    public function fetchSchedule(): PersistentScheduleItemCollection
    {
        return $this->fetchSchedule->fetch();
    }

    public function persistScheduleItem(PersistentScheduleItem $item): bool
    {
        return $this->persistScheduleItem->persist($item);
    }
}
