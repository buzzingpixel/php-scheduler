<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingTraversableTypeHintSpecification

namespace BuzzingPixel\Scheduler;

use function array_map;
use function array_values;
use function count;

readonly class PersistentScheduleItemCollection
{
    /** @var PersistentScheduleItem[] $scheduleItems */
    public array $scheduleItems;

    /** @param PersistentScheduleItem[] $scheduleItems */
    public function __construct(array $scheduleItems)
    {
        $this->scheduleItems = array_values(array_map(
            static fn (PersistentScheduleItem $q) => $q,
            $scheduleItems,
        ));
    }

    /** @phpstan-ignore-next-line */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->scheduleItems);
    }

    public function isEmpty(): bool
    {
        return count($this->scheduleItems) < 1;
    }
}
