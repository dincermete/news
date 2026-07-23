## [​](#introduction) Introduction

Filament comes with a “stats overview” widget template, which you can use to display a number of different stats in a single widget, without needing to write a custom view. Start by creating a widget with the command:

```
php artisan make:filament-widget StatsOverview --stats-overview
```

This command will create a new `StatsOverview.php` file. Open it, and return `Stat` instances from the `getStats()` method:

```
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Unique views', '192.1k'),
            Stat::make('Bounce rate', '21%'),
            Stat::make('Average time on page', '3:12'),
        ];
    }
}
```

Now, check out your widget in the dashboard.

Loading preview

![Stats overview](/docs/images/5.x/light/widgets/stats-overview/simple.jpg)![Stats overview](/docs/images/5.x/dark/widgets/stats-overview/simple.jpg)

## [​](#adding-a-description-and-icon-to-a-stat) Adding a description and icon to a stat

You may add a `description()` to provide additional information, along with a `descriptionIcon()`:

```
use Filament\Widgets\StatsOverviewWidget\Stat;

protected function getStats(): array
{
    return [
        Stat::make('Unique views', '192.1k')
            ->description('32k increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up'),
        Stat::make('Bounce rate', '21%')
            ->description('7% decrease')
            ->descriptionIcon('heroicon-m-arrow-trending-down'),
        Stat::make('Average time on page', '3:12')
            ->description('3% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up'),
    ];
}
```

The `descriptionIcon()` method also accepts a second parameter to put the icon before the description instead of after it:

```
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;

Stat::make('Unique views', '192.1k')
    ->description('32k increase')
    ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before)
```

Loading preview

![Stats overview with descriptions](/docs/images/5.x/light/widgets/stats-overview/description.jpg)![Stats overview with descriptions](/docs/images/5.x/dark/widgets/stats-overview/description.jpg)

## [​](#changing-the-color-of-the-stat) Changing the color of the stat

You may also give stats a [color](../styling/colors):

```
use Filament\Widgets\StatsOverviewWidget\Stat;

protected function getStats(): array
{
    return [
        Stat::make('Unique views', '192.1k')
            ->description('32k increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success'),
        Stat::make('Bounce rate', '21%')
            ->description('7% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger'),
        Stat::make('Average time on page', '3:12')
            ->description('3% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success'),
    ];
}
```

Loading preview

![Stats overview with colors](/docs/images/5.x/light/widgets/stats-overview/color.jpg)![Stats overview with colors](/docs/images/5.x/dark/widgets/stats-overview/color.jpg)

## [​](#adding-extra-html-attributes-to-a-stat) Adding extra HTML attributes to a stat

You may also pass extra HTML attributes to stats using `extraAttributes()`:

```
use Filament\Widgets\StatsOverviewWidget\Stat;

protected function getStats(): array
{
    return [
        Stat::make('Processed', '192.1k')
            ->color('success')
            ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
            ]),
        // ...
    ];
}
```

In this example, we are deliberately escaping the `$` in `$dispatch()` since this needs to be passed directly to the HTML, it is not a PHP variable.

## [​](#adding-a-chart-to-a-stat) Adding a chart to a stat

You may also add or chain a `chart()` to each stat to provide historical data. The `chart()` method accepts an array of data points to plot:

```
use Filament\Widgets\StatsOverviewWidget\Stat;

protected function getStats(): array
{
    return [
        Stat::make('Unique views', '192.1k')
            ->description('32k increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),
        // ...
    ];
}
```

Loading preview

![Stats overview with charts](/docs/images/5.x/light/widgets/stats-overview/chart.jpg)![Stats overview with charts](/docs/images/5.x/dark/widgets/stats-overview/chart.jpg)

## [​](#live-updating-stats-polling) Live updating stats (polling)

By default, stats overview widgets refresh their data every 5 seconds. To customize this, you may override the `$pollingInterval` property on the class to a new interval:

```
protected ?string $pollingInterval = '10s';
```

Alternatively, you may disable polling altogether:

```
protected ?string $pollingInterval = null;
```

## [​](#disabling-lazy-loading) Disabling lazy loading

By default, widgets are lazy-loaded. This means that they will only be loaded when they are visible on the page. To disable this behavior, you may override the `$isLazy` property on the widget class:

```
protected static bool $isLazy = false;
```

## [​](#adding-a-heading-and-description) Adding a heading and description

You may also add heading and description text above the widget by overriding the `$heading` and `$description` properties:

```
protected ?string $heading = 'Analytics';

protected ?string $description = 'An overview of some analytics.';
```

If you need to dynamically generate the heading or description text, you can instead override the `getHeading()` and `getDescription()` methods:

```
protected function getHeading(): ?string
{
    return 'Analytics';
}

protected function getDescription(): ?string
{
    return 'An overview of some analytics.';
}
```

Loading preview

![Stats overview with heading and description](/docs/images/5.x/light/widgets/stats-overview/heading.jpg)![Stats overview with heading and description](/docs/images/5.x/dark/widgets/stats-overview/heading.jpg)

[Edit this page on GitHub](https://github.com/filamentphp/filament/edit/5.x/packages/widgets/docs/02-stats-overview.md)

## Sponsored by

[![Kirschbaum](/docs/images/sponsors/footer/kirschbaum.svg)](https://kirschbaumdevelopment.com/solutions/filament-development "Kirschbaum")[![CMS Max](/docs/images/sponsors/footer/cms-max.svg)](https://cmsmax.com?ref=filamentphp.com "CMS Max")[![Baiz.ai](/docs/images/sponsors/footer/baiz-ai.svg)](https://baiz.ai "Baiz.ai")[![Agiledrop](/docs/images/sponsors/footer/agiledrop.svg)](https://www.agiledrop.com/laravel?utm_source=filament "Agiledrop")[![SerpApi](/docs/images/sponsors/footer/serpapi.svg)](https://serpapi.com/?utm_source=filamentphp "SerpApi")[![Mailtrap](/docs/images/sponsors/footer/mailtrap.svg)](https://mailtrap.io/email-sending?utm_source=community&utm_medium=referral&utm_campaign=filament "Mailtrap")[Your logo here](https://github.com/sponsors/danharrin)