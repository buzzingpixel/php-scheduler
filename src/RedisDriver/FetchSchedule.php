<?php

declare(strict_types=1);

namespace BuzzingPixel\Scheduler\RedisDriver;

use BuzzingPixel\Scheduler\PersistentScheduleItem;
use BuzzingPixel\Scheduler\PersistentScheduleItemCollection;
use BuzzingPixel\Scheduler\ScheduleFactory;
use BuzzingPixel\Scheduler\ScheduleItem;
use Symfony\Component\Cache\Adapter\RedisAdapter;

use function assert;

readonly class FetchSchedule
{
    public function __construct(
        private RedisAdapter $cachePool,
        private ScheduleFactory $scheduleFactory,
    ) {
    }

    public function fetch(): PersistentScheduleItemCollection
    {
        $schedule = $this->scheduleFactory->createSchedule();

        $items = $schedule->map(function (
            ScheduleItem $item,
        ): PersistentScheduleItem {
            $cache = $this->cachePool->getItem($item->key());

            $lastRunStartAt = null;

            $lastRunEndAt = null;

            if ($cache->isHit()) {
                $cacheItem = $cache->get();

                assert($cacheItem instanceof PersistentScheduleItem);

                $lastRunStartAt = $cacheItem->lastRunStartAt;

                $lastRunEndAt = $cacheItem->lastRunEndAt;
            }

            return new PersistentScheduleItem(
                $item->runEvery,
                $item->class,
                $item->method,
                $item->context,
                $lastRunStartAt,
                $lastRunEndAt,
            );
        });

        return new PersistentScheduleItemCollection($items);
    }
}
