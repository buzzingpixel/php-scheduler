<?php

declare(strict_types=1);

namespace BuzzingPixel\Scheduler\RedisDriver;

use BuzzingPixel\Scheduler\PersistentScheduleItem;
use BuzzingPixel\Scheduler\ShouldItemRun;
use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Redis;
use ReflectionProperty;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Throwable;

use function array_merge;
use function is_string;

readonly class RunSchedule
{
    private ClockInterface $clock;

    private string $redisNamespace;

    public function __construct(
        private Redis $redis,
        RedisAdapter $cachePool,
        private LoggerInterface $logger,
        private FetchSchedule $fetchSchedule,
        private ShouldItemRun $shouldItemRun,
        private ContainerInterface $container,
        private PersistScheduleItem $persistScheduleItem,
        ClockInterface|null $clock = null,
    ) {
        $redisNamespaceProperty = new ReflectionProperty(
            AbstractAdapter::class,
            'namespace',
        );

        /** @noinspection PhpExpressionResultUnusedInspection */
        $redisNamespaceProperty->setAccessible(true);

        $redisNamespace = $redisNamespaceProperty->getValue(
            $cachePool,
        );

        $redisNamespace = is_string($redisNamespace) ? $redisNamespace : '';

        $this->redisNamespace = $redisNamespace;

        $this->clock = $clock ?? new SystemClock(
            new DateTimeZone('UTC'),
        );
    }

    public function run(int $runExpiresAfterSeconds): void
    {
        $schedule = $this->fetchSchedule->fetch();

        if ($schedule->isEmpty()) {
            $this->logger->info('There are no scheduled commands');

            return;
        }

        try {
            $schedule->map(fn (
                PersistentScheduleItem $item,
            ) => $this->processItem($item, $runExpiresAfterSeconds));
        } catch (Throwable $exception) {
            $this->logException(
                'An unknown error occurred running a scheduled command',
                $exception,
            );
        }
    }

    private function processItem(
        PersistentScheduleItem $item,
        int $runExpiresAfterSeconds,
    ): void {
        try {
            $this->processItemInner($item, $runExpiresAfterSeconds);
        } catch (Throwable $exception) {
            // It may have been locked before the exception occurred so try
            // to unlock it
            $lockKey = $this->redisNamespace . 'lock_' . $item->key();

            $this->redis->del($lockKey);

            $this->logException(
                'There was a problem running a scheduled command',
                $exception,
                [
                    'className' => $item->class,
                    'runEvery' => $item->runEvery,
                ],
            );
        }
    }

    private function processItemInner(
        PersistentScheduleItem $item,
        int $runExpiresAfterSeconds,
    ): void {
        $lockKey = $this->redisNamespace . 'lock_' . $item->key();

        $lockObtained = $this->redis->setnx($lockKey, 'true');

        if (! $lockObtained) {
            $this->logger->info(
                $item->class . ' is currently running',
            );

            return;
        }

        $this->redis->expire($lockKey, $runExpiresAfterSeconds);

        if (! $this->shouldItemRun->check($item)) {
            $this->logger->info(
                $item->class . ' does not need to run at this time',
            );

            $this->redis->del($lockKey);

            return;
        }

        $start = $this->clock->now();

        $item = $item->with(lastRunStartAt: $start);

        $this->persistScheduleItem->persist($item);

        $scheduleItemInstance = $this->container->get($item->class);

        /** @phpstan-ignore-next-line */
        $scheduleItemInstance->{$item->method}($item->context);

        $end = $this->clock->now();

        $item = $item->with(lastRunEndAt: $end);

        $this->persistScheduleItem->persist($item);

        $this->redis->del($lockKey);

        $this->logger->info(
            $item->class . ' ran successfully',
            ['color' => 'green'],
        );
    }

    /** @param mixed[] $context */
    private function logException(
        string $message,
        Throwable $exception,
        array $context = [],
    ): void {
        $context = array_merge(
            $context,
            [
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
                'exception_code' => $exception->getCode(),
                'exception_file' => $exception->getFile(),
                'exception_line' => $exception->getLine(),
                'exception_trace' => $exception->getTraceAsString(),
            ],
        );

        $this->logger->critical(
            $message,
            $context,
        );
    }
}
