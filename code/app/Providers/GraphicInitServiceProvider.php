<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

use Event;
use Auth;

class GraphicInitServiceProvider extends ServiceProvider
{
    public function boot()
    {
        view()->composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();

                if ($view->offsetExists('currentuser') == false) {
                    $view->with('currentuser', $user);
                }

                if ($view->offsetExists('currentgas') == false) {
                    $view->with('currentgas', currentAbsoluteGas());
                }

                if ($view->offsetExists('currentcurrency') == false) {
                    $view->with('currentcurrency', $user->gas->currency);
                }
            }

            /*
                Qui vengono inizializzati alcuni valori di
                default utilizzati per i "widgets" in
                views/commons. Utile per evitare di sparpagliare
                di if() i relativi files
            */

            $defaults = [
                'squeeze' => false,
                'labelsize' => 4,
                'fieldsize' => 8,
                'help_text' => '',
                'help_popover' => '',
                'extras' => [],
                'prefix' => false,
                'postfix' => false,
                'triggering_modal' => false,
                'extra_class' => false,
                'extra_selection' => [],
                'multiple_select' => false,
            ];

            foreach ($defaults as $name => $value) {
                if ($view->offsetExists($name) == false) {
                    $view->with($name, $value);
                }
            }

            if ($view->offsetGet('squeeze') == true) {
                $view->with('fieldsize', 12);
            }
        });

        Paginator::useBootstrap();

        if (env('DUSK_TESTING', false)) {
            /*
                Sperabilmente questo finirà prima o poi direttamente in Laravel
                Dusk e sarà da rimuovere
                https://github.com/laravel/dusk/pull/895
            */
            \Laravel\Dusk\Browser::macro('typeAtXPath', function ($expression, $value) {
                $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::xpath($expression))->clear()->sendKeys($value);
                return $this;
            });

            \Laravel\Dusk\Browser::macro('assertInputValueAtXPath', function ($expression, $value) {
                $input_value = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::xpath($expression))->getAttribute('value');

                \PHPUnit\Framework\Assert::assertEquals(
                    $value,
                    $input_value,
                    "Expected value [{$value}] for the [{$expression}] input does not equal the actual value [${input_value}]."
                );

                return $this;
            });

            \Laravel\Dusk\Browser::macro('assertSeeAtXPath', function ($expression, $value) {
                $input_value = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::xpath($expression))->getText();

                \PHPUnit\Framework\Assert::assertEquals(
                    $value,
                    $input_value,
                    "Expected value [{$value}] for the [{$expression}] element does not equal the actual value [${input_value}]."
                );

                return $this;
            });

            \Laravel\Dusk\Browser::macro('scrollTop', function () {
                // @phpstan-ignore-next-line
                $this->script('document.documentElement.scrollTop = 0');
                return $this;
            });

            \Laravel\Dusk\Browser::macro('scrollBottom', function () {
                // @phpstan-ignore-next-line
                $this->script('window.scrollTo(0,document.body.scrollHeight)');
                return $this;
            });

            \Laravel\Dusk\Browser::macro('mainScreenshot', function ($filename) {
                // @phpstan-ignore-next-line
                $this->scrollTop()->pause(1000)->screenshot($filename);
                return $this;
            });
        }
    }

    public function register()
    {
    }
}
