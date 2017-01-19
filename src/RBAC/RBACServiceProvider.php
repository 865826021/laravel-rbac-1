<?php

namespace DmitryBubyakin\RBAC;

use Illuminate\Support\ServiceProvider;

class RBACServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__ . '/../migrations' => base_path('database/migrations')]);
        $this->publishes([__DIR__ . '/../config' => base_path('config')]);
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
}