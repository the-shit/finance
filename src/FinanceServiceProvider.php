<?php

namespace TheShit\Finance;

use Illuminate\Support\ServiceProvider;
use TheShit\Finance\Plaid\PlaidConnector;
use TheShit\Finance\Privacy\AmountBucketer;
use TheShit\Finance\Privacy\PrivacyTransformer;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/finance.php', 'finance');

        $this->app->singleton(PlaidConnector::class, function () {
            return new PlaidConnector(
                clientId:    config('finance.plaid.client_id'),
                secret:      config('finance.plaid.secret'),
                environment: config('finance.plaid.environment'),
            );
        });

        $this->app->singleton(PrivacyTransformer::class, function () {
            return new PrivacyTransformer(
                bucketer: new AmountBucketer,
                driver:   config('finance.privacy.driver', 'ollama'),
                model:    config('finance.privacy.model'),
                endpoint: config('finance.privacy.endpoint'),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/finance.php' => config_path('finance.php'),
            ], 'finance-config');
        }
    }
}
