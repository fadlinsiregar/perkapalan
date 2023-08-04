<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('riskMatrix', function ($expression) {
            $conditions = explode(',', $expression);
            $likelihoodLevel = trim($conditions[0]);
            $consequencesLevel = trim($conditions[1]);

            $riskMatrix = [
                'Rare' => ['Insignificant' => 'bg-success', 'Minor' => 'bg-success', 'Significant' => 'bg-warning', 'Major' => 'bg-warning', 'Catastrophic' => 'bg-danger'],
                'Unlikely' => ['Insignificant' => 'bg-success', 'Minor' => 'bg-warning', 'Significant' => 'bg-warning', 'Major' => 'bg-danger', 'Catastrophic' => 'bg-danger'],
                'Possible' => ['Insignificant' => 'bg-warning', 'Minor' => 'bg-warning', 'Significant' => 'bg-danger', 'Major' => 'bg-danger', 'Catastrophic' => 'bg-danger'],
                'Likely' => ['Insignificant' => 'bg-warning', 'Minor' => 'bg-danger', 'Significant' => 'bg-danger', 'Major' => 'bg-danger', 'Catastrophic' => 'bg-danger'],
                'Almost Certain' => ['Insignificant' => 'bg-danger', 'Minor' => 'bg-danger', 'Significant' => 'bg-danger', 'Major' => 'bg-danger', 'Catastrophic' => 'bg-danger'],
            ];

            return isset($riskMatrix[$likelihoodLevel][$consequencesLevel]) ? $riskMatrix[$likelihoodLevel][$consequencesLevel] : 'bg-secondary';
        });
    }
}
