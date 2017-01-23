<?php

namespace DmitryBubyakin\Rbac;

use Illuminate\Support\ServiceProvider;

class RbacServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $migration      = 'create_rbac_tables.php';
        $datedMigration = $this->getMigrationName($migration);

        $this->publishes([
            __DIR__ . '/../migrations/' . $migration => base_path('database/migrations/' . $datedMigration)
        ]);
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

    protected function getMigrationName($name)
    {
        return sprintf('%s_%s', date('Y_m_d_His'), $name);
    }

}