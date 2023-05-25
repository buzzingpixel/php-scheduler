# BuzzingPixel PHP Scheduler

A fairly simple php scheduling system you can add to nearly any PHP app that utilizes a PSR-11 container.

## Drivers

At the moment, the only driver supplied with this package is the Redis driver. But if you want to supply your own driver, just implement the `\BuzzingPixel\Scheduler\ScheduleHandler` interface and wire your container to supply that when the interface is requested.

Otherwise, wire `\BuzzingPixel\Scheduler\ScheduleHandler` to supply the `\BuzzingPixel\Scheduler\RedisDriver\RedisScheduleHandler`.

PHP's `\Redis` and the Symfony `\Symfony\Component\Cache\Adapter\RedisAdapter` will need to also be available through the constructor/container.

## Usage

### ScheduleFactory

You will need to implement the `\BuzzingPixel\Scheduler\ScheduleFactory` and make it available through your container and configure to send whatever scheduled items you want.

Example:

```php
<?php

declare(strict_types=1);

use App\SomeScheduledClass;
use BuzzingPixel\Scheduler\Frequency;
use BuzzingPixel\Scheduler\ScheduleItem;
use BuzzingPixel\Scheduler\ScheduleItemCollection;

class ScheduleFactory implements \BuzzingPixel\Scheduler\ScheduleFactory
{
    public function createSchedule(): ScheduleItemCollection
    {
        return new ScheduleItemCollection([
            new ScheduleItem(
                Frequency::FIVE_MINUTES,
                SomeScheduledClass::class,
                // Optionally provide a method, otherwise it will default to __invoke
                'myMethod',
                // Optionally send a context array that will be passed as the first argument to your method
                [
                    'foo' => 'bar',
                ],
            ),
        ]);
    }
}
```

### Midnight Strings

If you utilize the Frequency midnight strings, you might want to be able to control the timezone. Since system timezone can be unreliable and/or it's recommended practice to set that to UTC, this package does not rely on the system's timezone. What you can do instead is send your own newed up instance of `\BuzzingPixel\Scheduler\SchedulerTimeZone` through the container.

Here's an example:

```php
$containerBindings->addBinding(
    \BuzzingPixel\Scheduler\SchedulerTimeZone::class,
    static fn () => new \BuzzingPixel\Scheduler\SchedulerTimeZone(
        new \DateTimeZone('US/Central'),
    ),
);
```

### Running the schedule

Once your configuration is in place and you've set up some scheduled jobs to run, you need to set up something to call `\BuzzingPixel\Scheduler\ScheduleHandler::runSchedule` every minute. You can use a cron, or set up a Docker image or whatever works best in your environment.

#### Command to run schedule

This package provides a Symfony console command which you can use if you're using [Symfony Console](https://symfony.com/doc/current/components/console.html) (or [Silly](https://github.com/mnapoli/silly), which is my preference). Load up `\BuzzingPixel\Scheduler\Framework\RunScheduleSymfonyCommand` through your container, and add it to your Symfony console app.

Then in your cron or Docker setup run the command `buzzingpixel-schedule:run` (unless you've changed the command name) every minute through your CLI app.

##### Changing the command name

If you'd like to change the command name, you can do so through the `name` constructor parameter of the `RunScheduleSymfonyCommand` class. Configure your DI to provide the command name you would prefer.
