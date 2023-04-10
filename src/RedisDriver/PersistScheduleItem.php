<?php

declare(strict_types=1);

namespace BuzzingPixel\Scheduler\RedisDriver;

use BuzzingPixel\Scheduler\PersistentScheduleItem;
use Symfony\Component\Cache\Adapter\RedisAdapter;

readonly class PersistScheduleItem
{
    public function __construct(private RedisAdapter $cachePool)
    {
    }

    public function persist(PersistentScheduleItem $item): bool
    {
        return $this->cachePool->save($this->cachePool->getItem(
            $item->key(),
        )->set($item));
    }
}
