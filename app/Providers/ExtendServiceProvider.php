<?php

namespace App\Providers;

use App\Support\ChatGPT;
use App\Support\OpenAI;
use App\Support\PushDeer;
use App\Traits\Conditionable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ExtendServiceProvider extends ServiceProvider implements DeferrableProvider
{
    use Conditionable;

    /**
     * All of the container singletons that should be registered.
     *
     * @var string[]
     */
    public $singletons = [];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerChatGPT();
        $this->registerOpenAI();
        $this->registerPushDeer();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the events that trigger this service provider to register.
     *
     * @return string[]
     */
    public function when()
    {
        return [];
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            ChatGPT::class, 'chatgpt',
            OpenAI::class, 'openai',
            PushDeer::class, 'pushdeer',
        ];
    }

    /**
     * @return void
     */
    protected function registerOpenAI(): void
    {
        $this->app->singleton(OpenAI::class, function (Application $application) {
            return new OpenAI($application['config']['services.openai']);
        });

        $this->app->alias(OpenAI::class, 'openai');
    }

    /**
     * @return void
     */
    protected function registerPushDeer(): void
    {
        $this->app->singleton(PushDeer::class, function (Application $application) {
            return new PushDeer($application['config']['services.pushdeer']);
        });

        $this->app->alias(PushDeer::class, 'pushdeer');
    }

    /**
     * @return void
     */
    protected function registerChatGPT(): void
    {
        $this->app->singleton(ChatGPT::class, function (Application $application) {
            return new ChatGPT($application['config']['services.chatgpt']);
        });

        $this->app->alias(ChatGPT::class, 'chatgpt');
    }
}
