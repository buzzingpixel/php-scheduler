<?php

declare(strict_types=1);

namespace BuzzingPixel\Scheduler;

interface ScheduleHandler
{
    public const RUN_EXPIRES_AFTER_SECONDS = 300;

    public function runExpiresAfterSeconds(): int;

    /**
     * Runs items that are scheduled to be run if it is time for them to run
     */
    public function runSchedule(): void;

    /**
     * Fetches the configured schedule items and adds any backing info from
     * persistence such as lastRunStartAt and lastRunEndAt
     */
    public function fetchSchedule(): PersistentScheduleItemCollection;

    /**
     *  Persists the schedule items state
     */
    public function persistScheduleItem(PersistentScheduleItem $item): bool;
}
