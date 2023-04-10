<?php

declare(strict_types=1);

namespace BuzzingPixel\Scheduler;

use function is_numeric;

enum Frequency: string
{
    case ALWAYS                = '0';
    case FIVE_MINUTES          = '5';
    case TEN_MINUTES           = '10';
    case THIRTY_MINUTES        = '30';
    case HOUR                  = '60';
    case DAY                   = '1440';
    case WEEK                  = '10080';
    case MONTH                 = '43800';
    case DAY_AT_MIDNIGHT       = 'DAY_AT_MIDNIGHT';
    case SATURDAY_AT_MIDNIGHT  = 'SATURDAY_AT_MIDNIGHT';
    case SUNDAY_AT_MIDNIGHT    = 'SUNDAY_AT_MIDNIGHT';
    case MONDAY_AT_MIDNIGHT    = 'MONDAY_AT_MIDNIGHT';
    case TUESDAY_AT_MIDNIGHT   = 'TUESDAY_AT_MIDNIGHT';
    case WEDNESDAY_AT_MIDNIGHT = 'WEDNESDAY_AT_MIDNIGHT';
    case THURSDAY_AT_MIDNIGHT  = 'THURSDAY_AT_MIDNIGHT';
    case FRIDAY_AT_MIDNIGHT    = 'FRIDAY_AT_MIDNIGHT';

    public function isMinuteBased(): bool
    {
        return is_numeric($this->value);
    }

    public function isMidnightString(): bool
    {
        return ! $this->isMinuteBased();
    }

    public function getMinutes(): int|null
    {
        return $this->isMinuteBased() ? ((int) $this->value) : null;
    }

    public function getSeconds(): int|null
    {
        return $this->isMinuteBased() ? ((int) $this->value) * 60 : null;
    }
}
