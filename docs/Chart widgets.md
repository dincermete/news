## [​](#introduction) Introduction

Filament comes with many “chart” widget templates, which you can use to display real-time, interactive charts. Start by creating a widget with the command:

```
php artisan make:filament-widget BlogPostsChart --chart
```

There is a single `ChartWidget` class that is used for all charts. The type of chart is set by the `getType()` method. In this example, that method returns the string `'line'`. The `protected ?string $heading` variable is used to set the heading that describes the chart. If you need to set the heading dynamically, you can override the `getHeading()` method. The `getData()` method is used to return an array of datasets and labels. Each dataset is a labeled array of points to plot on the chart, and each label is a string. This structure is identical to the [Chart.js](https://www.chartjs.org/docs) library, which Filament uses to render charts. You may use the [Chart.js documentation](https://www.chartjs.org/docs) to fully understand the possibilities to return from `getData()`, based on the chart type.

```
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class BlogPostsChart extends ChartWidget
{
    protected ?string $heading = 'Blog Posts';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Blog posts created',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
```

Now, check out your widget in the dashboard.

Loading preview

![Line chart](/docs/images/5.x/light/widgets/chart/line.jpg)![Line chart](/docs/images/5.x/dark/widgets/chart/line.jpg)

## [​](#available-chart-types) Available chart types

Below is a list of available chart widget classes which you may extend, and their corresponding [Chart.js](https://www.chartjs.org/docs) documentation page, for inspiration on what to return from `getData()`:

- Bar chart - [Chart.js documentation](https://www.chartjs.org/docs/latest/charts/bar)
- Bubble chart - [Chart.js documentation](https://www.chartjs.org/docs/latest/charts/bubble)
- Doughnut chart - [Chart.js documentation](https://www.chartjs.org/docs/latest/charts/doughnut)
- Line chart - [Chart.js documentation](https://www.chartjs.org/docs/latest/charts/line)
- Pie chart - [Chart.js documentation](https://www.chartjs.org/docs/latest/charts/doughnut.html#pie)
- Polar area chart - [Chart.js documentation](https://www.chartjs.org/docs/latest/charts/polar)
- Radar chart - [Chart.js documentation](https://www.chartjs.org/docs/latest/charts/radar)
- Scatter chart - [Chart.js documentation](https://www.chartjs.org/docs/latest/charts/scatter)

For example, you could use a bar chart by returning `'bar'` from the `getType()` method:

Loading preview

![Bar chart](/docs/images/5.x/light/widgets/chart/bar.jpg)![Bar chart](/docs/images/5.x/dark/widgets/chart/bar.jpg)

Here are examples of the other available chart types:

Loading preview

![Pie chart](/docs/images/5.x/light/widgets/chart/pie.jpg)![Pie chart](/docs/images/5.x/dark/widgets/chart/pie.jpg)

Loading preview

![Doughnut chart](/docs/images/5.x/light/widgets/chart/doughnut.jpg)![Doughnut chart](/docs/images/5.x/dark/widgets/chart/doughnut.jpg)

Loading preview

![Radar chart](/docs/images/5.x/light/widgets/chart/radar.jpg)![Radar chart](/docs/images/5.x/dark/widgets/chart/radar.jpg)

Loading preview

![Polar area chart](/docs/images/5.x/light/widgets/chart/polar-area.jpg)![Polar area chart](/docs/images/5.x/dark/widgets/chart/polar-area.jpg)

Loading preview

![Scatter chart](/docs/images/5.x/light/widgets/chart/scatter.jpg)![Scatter chart](/docs/images/5.x/dark/widgets/chart/scatter.jpg)

Loading preview

![Bubble chart](/docs/images/5.x/light/widgets/chart/bubble.jpg)![Bubble chart](/docs/images/5.x/dark/widgets/chart/bubble.jpg)

## [​](#customizing-the-chart-color) Customizing the chart color

You can customize the [color](/docs/5.x/styling/colors) of the chart data by setting the `$color` property:

```
protected string $color = 'info';
```

If you’re looking to customize the color further, or use multiple colors across multiple datasets, you can still make use of Chart.js’s [color options](https://www.chartjs.org/docs/latest/general/colors.html) in the data:

```
protected function getData(): array
{
    return [
        'datasets' => [
            [
                'label' => 'Blog posts created',
                'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                'backgroundColor' => '#36A2EB',
                'borderColor' => '#9BD0F5',
            ],
        ],
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    ];
}
```

## [​](#generating-chart-data-from-an-eloquent-model) Generating chart data from an Eloquent model

To generate chart data from an Eloquent model, Filament recommends that you install the `flowframe/laravel-trend` package. You can view the [documentation](https://github.com/Flowframe/laravel-trend). Here is an example of generating chart data from a model using the `laravel-trend` package:

```
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

protected function getData(): array
{
    $data = Trend::model(BlogPost::class)
        ->between(
            start: now()->startOfYear(),
            end: now()->endOfYear(),
        )
        ->perMonth()
        ->count();

    return [
        'datasets' => [
            [
                'label' => 'Blog posts',
                'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
            ],
        ],
        'labels' => $data->map(fn (TrendValue $value) => $value->date),
    ];
}
```

## [​](#filtering-chart-data) Filtering chart data

### [​](#basic-select-filter) Basic Select filter

You can set up chart filters to change the data that is presented. Commonly, this is used to change the time period that chart data is rendered for. To set a default filter value, set the `$filter` property:

```
public ?string $filter = 'today';
```

Then, define the `getFilters()` method to return an array of values and labels for your filter:

```
protected function getFilters(): ?array
{
    return [
        'today' => 'Today',
        'week' => 'Last week',
        'month' => 'Last month',
        'year' => 'This year',
    ];
}
```

You can use the active filter value within your `getData()` method:

```
protected function getData(): array
{
    $activeFilter = $this->filter;

    // ...
}
```

Loading preview

![Chart with filter](/docs/images/5.x/light/widgets/chart/filter.jpg)![Chart with filter](/docs/images/5.x/dark/widgets/chart/filter.jpg)

The `$filter` property is user-controllable. Although the `<select>` element only offers the keys returned from `getFilters()`, a crafted request can set `$this->filter` to any string, so it is not limited to those keys. You must ensure the value is valid before using it in a query — for example, by checking it against the keys of `getFilters()`, or by using a `match` expression with a safe default. Never interpolate `$this->filter` directly into a raw query.

### [​](#custom-filters) Custom filters

You can use [schema components](/docs/5.x/schemas) to build custom filters for your chart widget. This approach offers a more flexible way to define filters. To get started, use the `HasFiltersSchema` trait and implement the `filtersSchema()` method:

```
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class BlogPostsChart extends ChartWidget
{
    use HasFiltersSchema;
    
    // ...
    
    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('startDate')
                ->default(now()->subDays(30)),
            DatePicker::make('endDate')
                ->default(now()),
        ]);
    }
}
```

The filter values are accessible via the `$this->filters` array. You can use these values inside your `getData()` method:

```
protected function getData(): array
{
    $startDate = $this->filters['startDate'] ?? null;
    $endDate = $this->filters['endDate'] ?? null;

    return [
        // ...
    ];
}
```

The `$this->filters` array will always reflect the current form data. Please note that this data is not validated, as it is available live and not intended to be used for anything other than querying the database. You must ensure that the data is valid before using it.

Loading preview

![Chart with custom filters](/docs/images/5.x/light/widgets/chart/custom-filters.jpg)![Chart with custom filters](/docs/images/5.x/dark/widgets/chart/custom-filters.jpg)

If you want to add filters that apply to multiple widgets at once, see [filtering widget data](/docs/5.x/widgets/overview#filtering-widget-data) in the dashboard.

#### [​](#deferring-filter-updates) Deferring filter updates

By default, filters using the `filtersSchema()` method update the chart data immediately as they are changed. However, for complex queries or better user experience, you may want to **defer** filter updates until the user clicks an “Apply” button. When deferred, filter changes are only applied when the user clicks the “Apply” button. This ensures that the chart only re-renders when the user has finished adjusting all of their filters. The chart will display data using the default filter values when the page first loads, ensuring users see meaningful data immediately without needing to take action. To enable deferred filters, set the `$hasDeferredFilters` property to `true`:

```
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class BlogPostsChart extends ChartWidget
{
    use HasFiltersSchema;

    protected bool $hasDeferredFilters = true;

    // ...
}
```

If you need dynamic control over whether filters are deferred, you may override the `hasDeferredFilters()` method:

```
public function hasDeferredFilters(): bool
{
    return auth()->user()->prefersDeferredFilters();
}
```

#### [​](#resetting-filters-to-defaults) Resetting filters to defaults

When using deferred filters, a “Reset” link appears in the filter dropdown footer alongside the “Apply” button. Clicking this link restores all filters to their default values as defined in the `filtersSchema()` method. For example, if you set `->default(now()->subDays(30))` on a `DatePicker`, the reset action will restore that default date, not an empty value.

#### [​](#customizing-filter-actions) Customizing filter actions

You may customize the apply and reset actions that appear when using deferred filters. All methods that are available to [customize action trigger buttons](/docs/5.x/actions/overview) can be used:

```
use Filament\Actions\Action;

public function filtersApplyAction(Action $action): Action
{
    return $action
        ->label('Update Chart')
        ->color('success');
}

public function filtersResetAction(Action $action): Action
{
    return $action
        ->label('Clear Filters')
        ->color('danger');
}
```

## [​](#empty-state) Empty state

When the `getData()` method returns an empty array, the chart widget renders an “empty state” instead of the chart. To customize when the empty state is rendered, override the `isEmpty()` method:

```
public function isEmpty(): bool
{
    $data = $this->getCachedData();

    return empty($data['datasets'][0]['data'] ?? []);
}
```

### [​](#setting-the-empty-state-heading) Setting the empty state heading

To customize the heading of the empty state, set the `$emptyStateHeading` property:

```
protected ?string $emptyStateHeading = 'No data available';
```

Alternatively, you can override the `getEmptyStateHeading()` method to return a dynamic heading:

```
use Illuminate\Contracts\Support\Htmlable;

public function getEmptyStateHeading(): string | Htmlable
{
    return "No sales yet for {$this->filter}";
}
```

### [​](#setting-the-empty-state-description) Setting the empty state description

To customize the description of the empty state, set the `$emptyStateDescription` property:

```
protected ?string $emptyStateDescription = 'Check back later once data has been collected.';
```

Alternatively, you can override the `getEmptyStateDescription()` method to return a dynamic description:

```
use Illuminate\Contracts\Support\Htmlable;

public function getEmptyStateDescription(): string | Htmlable | null
{
    return 'Sales data will appear here once orders are placed.';
}
```

### [​](#setting-the-empty-state-icon) Setting the empty state icon

To customize the [icon](/docs/5.x/styling/icons) of the empty state, set the `$emptyStateIcon` property:

```
use Filament\Support\Icons\Heroicon;

protected string | BackedEnum | null $emptyStateIcon = Heroicon::OutlinedChartBar;
```

Alternatively, you can override the `getEmptyStateIcon()` method to return a dynamic icon:

```
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

public function getEmptyStateIcon(): string | BackedEnum | Htmlable
{
    return Heroicon::OutlinedShoppingCart;
}
```

### [​](#adding-empty-state-actions) Adding empty state actions

You can add [actions](/docs/5.x/actions/overview) to the empty state to prompt users to take action by overriding the `getEmptyStateActions()` method:

```
use Filament\Actions\Action;

public function getEmptyStateActions(): array
{
    return [
        Action::make('refresh')
            ->label('Refresh')
            ->action('refresh'),
    ];
}
```

### [​](#using-a-custom-empty-state-view) Using a custom empty state view

You may use a completely custom empty state view by overriding the `getEmptyState()` method:

```
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;

public function getEmptyState(): View | Htmlable | null
{
    return view('widgets.charts.custom-empty-state');
}
```

## [​](#live-updating-chart-data-polling) Live updating chart data (polling)

By default, chart widgets refresh their data every 5 seconds. To customize this, you may override the `$pollingInterval` property on the class to a new interval:

```
protected ?string $pollingInterval = '10s';
```

Alternatively, you may disable polling altogether:

```
protected ?string $pollingInterval = null;
```

## [​](#setting-a-maximum-chart-height) Setting a maximum chart height

You may place a maximum height on the chart to ensure that it doesn’t get too big, using the `$maxHeight` property:

```
protected ?string $maxHeight = '300px';
```

Loading preview

![Chart with maximum height](/docs/images/5.x/light/widgets/chart/max-height.jpg)![Chart with maximum height](/docs/images/5.x/dark/widgets/chart/max-height.jpg)

## [​](#setting-chart-configuration-options) Setting chart configuration options

You may specify an `$options` variable on the chart class to control the many configuration options that the Chart.js library provides. For instance, you could turn off the [legend](https://www.chartjs.org/docs/latest/configuration/legend.html) for a line chart:

```
protected ?array $options = [
    'plugins' => [
        'legend' => [
            'display' => false,
        ],
    ],
];
```

Alternatively, you can override the `getOptions()` method to return a dynamic array of options:

```
protected function getOptions(): array
{
    return [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
    ];
}
```

These PHP arrays will get transformed into JSON objects when the chart is rendered. If you want to return raw JavaScript from this method instead, you can return a `RawJs` object. This is useful if you want to use a JavaScript callback function, for example:

```
use Filament\Support\RawJs;

protected function getOptions(): RawJs
{
    return RawJs::make(<<<JS
        {
            scales: {
                y: {
                    ticks: {
                        callback: (value) => '€' + value,
                    },
                },
            },
        }
    JS);
}
```

## [​](#adding-a-description) Adding a description

You may add a description, below the heading of the chart, using the `getDescription()` method:

```
public function getDescription(): ?string
{
    return 'The number of blog posts published per month.';
}
```

Loading preview

![Chart with description](/docs/images/5.x/light/widgets/chart/description.jpg)![Chart with description](/docs/images/5.x/dark/widgets/chart/description.jpg)

## [​](#disabling-lazy-loading) Disabling lazy loading

By default, widgets are lazy-loaded. This means that they will only be loaded when they are visible on the page. To disable this behavior, you may override the `$isLazy` property on the widget class:

```
protected static bool $isLazy = false;
```

## [​](#making-the-chart-collapsible) Making the chart collapsible

You may allow the chart to be collapsible by setting the `$isCollapsible` property on the widget class to be `true`:

```
protected bool $isCollapsible = true;
```

Loading preview

![Collapsible chart](/docs/images/5.x/light/widgets/chart/collapsible.jpg)![Collapsible chart](/docs/images/5.x/dark/widgets/chart/collapsible.jpg)

## [​](#using-custom-chart-js-plugins) Using custom Chart.js plugins

Chart.js offers a powerful plugin system that allows you to extend its functionality and create custom chart behaviors. This guide details how to use them in a chart widget.

### [​](#step-1-install-the-plugin-with-npm) Step 1: Install the plugin with NPM

To start with, install the plugin using NPM into your project. In this guide, we will install [`chartjs-plugin-datalabels`](https://chartjs-plugin-datalabels.netlify.app/guide/getting-started.html#installation):

```
npm install chartjs-plugin-datalabels --save-dev
```

### [​](#step-2-create-a-javascript-file-importing-the-plugin) Step 2: Create a JavaScript file importing the plugin

Create a new JavaScript file where you will define your custom plugin. In this guide, we’ll call it `filament-chart-js-plugins.js`. Import the plugin, and add it to the `window.filamentChartJsPlugins` array:

```
import ChartDataLabels from 'chartjs-plugin-datalabels'


window.filamentChartJsPlugins ??= []
window.filamentChartJsPlugins.push(ChartDataLabels)
```

This is equivalent to including the plugins “inline” via `new Chart(..., { plugins: [...] })` when instantiating a Chart.js chart. It’s important to initialise the array if it has not been already, before pushing onto it. This ensures that multiple JavaScript files (especially those from Filament plugins) that register Chart.js plugins do not overwrite each other, regardless of the order they are booted in. You can push as many plugins to the array as you would like to install, you do not need a separate file to import each plugin. Additionally, you can also register any “global plugins” which will use `Chart.register([...])` in the `window.filamentChartJsGlobalPlugins` array:

```
import ChartDataLabels from 'chartjs-plugin-datalabels'


window.filamentChartJsGlobalPlugins ??= []
window.filamentChartJsGlobalPlugins.push(ChartDataLabels)
```

### [​](#step-3-compile-the-javascript-file-with-vite) Step 3: Compile the JavaScript file with Vite

Now, you need to build the JavaScript file with Vite, or your bundler of choice. Include the file in your Vite configuration (usually `vite.config.js`). For example:

```
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',
                'resources/js/filament-chart-js-plugins.js', // Include the new file in the `input` array so it is built
            ],
        }),
    ],
});
```

Build the file with `npm run build`.

### [​](#step-4-register-the-javascript-file-in-filament) Step 4: Register the JavaScript file in Filament

Filament needs to know to include this JavaScript file when rendering chart widgets. You can do this in the `boot()` method of a service provider like `AppServiceProvider`:

```
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Vite;

FilamentAsset::register([
    Js::make('chart-js-plugins', Vite::asset('resources/js/filament-chart-js-plugins.js'))->module(),
]);
```

You can find out more about [asset registration](/docs/5.x/advanced/assets), and even [register assets for a specific panel](/docs/5.x/panel-configuration#registering-assets-for-a-panel).

[Edit this page on GitHub](https://github.com/filamentphp/filament/edit/5.x/packages/widgets/docs/03-charts.md)

## Sponsored by

[![Kirschbaum](/docs/images/sponsors/footer/kirschbaum.svg)](https://kirschbaumdevelopment.com/solutions/filament-development "Kirschbaum")[![CMS Max](/docs/images/sponsors/footer/cms-max.svg)](https://cmsmax.com?ref=filamentphp.com "CMS Max")[![SerpApi](/docs/images/sponsors/footer/serpapi.svg)](https://serpapi.com/?utm_source=filamentphp "SerpApi")[![Mailtrap](/docs/images/sponsors/footer/mailtrap.svg)](https://mailtrap.io/email-sending?utm_source=community&utm_medium=referral&utm_campaign=filament "Mailtrap")[![Baiz.ai](/docs/images/sponsors/footer/baiz-ai.svg)](https://baiz.ai "Baiz.ai")[![Agiledrop](/docs/images/sponsors/footer/agiledrop.svg)](https://www.agiledrop.com/laravel?utm_source=filament "Agiledrop")[Your logo here](https://github.com/sponsors/danharrin)