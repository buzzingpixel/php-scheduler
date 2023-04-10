<?php

declare(strict_types=1);

namespace BuzzingPixel\Scheduler;

use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Psr\Clock\ClockInterface;

readonly class ShouldItemRun
{
    private ClockInterface $clock;

    private DateTimeZone $timeZone;

    public function __construct(
        ClockInterface|null $clock = null,
        SchedulerTimeZone|null $schedulerTimeZone = null,
    ) {
        $schedulerTimeZone ??= new SchedulerTimeZone();

        $this->clock = $clock ?? SystemClock::fromUTC();

        $this->timeZone = $schedulerTimeZone->timeZone;
    }

    public function check(PersistentScheduleItem $item): bool
    {
        $currentTime = $this->clock->now()->setTimezone(
            $this->timeZone,
        );

        $currentTimeStamp = $currentTime->getTimestamp();

        $lastRunStartAt = $item->lastRunStartAt;

        $lastRunTimeStamp = 0;

        if ($lastRunStartAt !== null) {
            $lastRunTimeStamp = $lastRunStartAt->getTimestamp();
        }

        $secondsSinceLastRun = $currentTimeStamp - $lastRunTimeStamp;

        // If runEvery is minute based we'll check if it's time to run based on that
        if ($item->runEvery->getMinutes() !== null) {
            return $secondsSinceLastRun >= $item->runEvery->getSeconds();
        }

        /**
         * Now we know it's a midnight string and we're checking for that
         */

        // Increment timestamp by 20 hours
        $incrementTime = $lastRunTimeStamp + 72000;

        /**
         * Don't run if it hasn't been more than 20 hours (we're trying to
         * hit the right window, but we can't be too precise because what if
         * the cron doesn't run right at midnight. But we also only want to
         * run this once)
         */
        if ($incrementTime > $currentTimeStamp) {
            return false;
        }

        // If the hour is not in the midnight range, we know we can stop
        if ($currentTime->format('H') !== '00') {
            return false;
        }

        // Now if we're running every day, we know it's time to run
        if ($item->runEvery->name === Frequency::DAY_AT_MIDNIGHT->name) {
            return true;
        }

        $day = $currentTime->format('l');

        if (
            $item->runEvery->name === Frequency::SATURDAY_AT_MIDNIGHT->name &&
            $day === 'Saturday'
        ) {
            return true;
        }

        if (
            $item->runEvery->name === Frequency::SUNDAY_AT_MIDNIGHT->name &&
            $day === 'Sunday'
        ) {
            return true;
        }

        if (
            $item->runEvery->name === Frequency::MONDAY_AT_MIDNIGHT->name &&
            $day === 'Monday'
        ) {
            return true;
        }

        if (
            $item->runEvery->name === Frequency::TUESDAY_AT_MIDNIGHT->name &&
            $day === 'Tuesday'
        ) {
            return true;
        }

        if (
            $item->runEvery->name === Frequency::WEDNESDAY_AT_MIDNIGHT->name &&
            $day === 'Wednesday'
        ) {
            return true;
        }

        if (
            $item->runEvery->name === Frequency::THURSDAY_AT_MIDNIGHT->name &&
            $day === 'Thursday'
        ) {
            return true;
        }

        return $item->runEvery->name === Frequency::FRIDAY_AT_MIDNIGHT->name &&
            $day === 'Friday';
    }
}
