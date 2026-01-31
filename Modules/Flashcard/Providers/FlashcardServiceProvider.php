<?php

namespace Modules\Flashcard\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Flashcard\Console\FlashcardInteractiveCommand;
use Nwidart\Modules\Traits\PathNamespace;

class FlashcardServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Flashcard';

    protected string $nameLower = 'flashcard';

    public function boot(): void
    {
        $this->registerCommands();
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));
    }

    protected function registerCommands(): void
    {
        $this->commands([
            FlashcardInteractiveCommand::class,
        ]);
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}
