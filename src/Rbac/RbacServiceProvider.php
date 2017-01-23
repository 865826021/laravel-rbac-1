<?php

namespace DmitryBubyakin\Rbac;

use Illuminate\Support\ServiceProvider;

class RbacServiceProvider extends ServiceProvider
{

    /**
     * Migration file name
     */
    const MIGRATION = 'create_rbac_tables.php';
    /**
     * Migrations path
     */
    const MIGRATION_PATH = 'database/migrations/';

    public function boot()
    {
        if (!$this->migrationExists()) {
            $this->publishes([
                __DIR__ . '/../migrations/' . static::MIGRATION => $this->getMigrationName()
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config' => base_path('config')
        ]);
        $this->publishes([
            __DIR__ . '/../../tests' => base_path('tests/Rbac')
        ]);
    }

    public function register()
    {
        $this->registerBlade();
    }

    /**
     * Register blade directives
     * @return void
     */
    public function registerBlade()
    {
        \Blade::directive('perm', function ($expression) {
            return "<?php if (!\\Auth::guest() && \\Auth::user()->can($expression)): ?>";
        });
        \Blade::directive('elseperm', function ($expression = null) {
            return $expression
                ? "<?php elseif (!\\Auth::guest() && \\Auth::user()->can($expression)): ?>"
                : "<?php else: ?>";
        });
        \Blade::directive('endperm', function () {
            return "<?php endif; ?>";
        });

        \Blade::directive('role', function ($expression) {
            return "<?php if (!\\Auth::guest() && \\Auth::user()->roleIs($expression)): ?>";
        });
        \Blade::directive('elserole', function ($expression = null) {
            return $expression
                ? "<?php elseif (!\\Auth::guest() && \\Auth::user()->roleIs($expression)): ?>"
                : "<?php else: ?>";
        });
        \Blade::directive('endrole', function () {
            return "<?php endif; ?>";
        });
    }

    /**
     * Get migration name Y_m_d_His_migration_name
     * @return string
     */
    protected function getMigrationName()
    {
        return static::MIGRATION_PATH . date('Y_m_d_His') . '_' . static::MIGRATION;
    }

    /**
     * Check if migration exists
     * @return bool
     */
    protected function migrationExists()
    {
        return !empty(glob(base_path(static::MIGRATION_PATH . '*' . static::MIGRATION)));
    }

}