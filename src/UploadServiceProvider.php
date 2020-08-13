<?php

namespace Inewtonua\Upload;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Inewtonua\Upload\Contracts\UploadModel as UploadModelContract;
use Inewtonua\Upload\Models\Upload as UploadModel;
use Inewtonua\Upload\Contracts\Upload as UploadContract;
use Inewtonua\Upload\Console\RemoveTemporaryFiles;
use Inewtonua\Upload\Console\ReGenerateImages;
use SebastianBergmann\Environment\Console;
use Illuminate\Console\Scheduling\Schedule;


class UploadServiceProvider extends ServiceProvider
{

    public function boot(Router $router, GateContract $gate)
    {

        $this->registerRoutes($router);
        $this->registerGates($gate);

        /**
         * Валидация: Максимально разрешенный общий размер файлов
         */
        Validator::extend('max_uploaded_file_size', function ($attribute, $value, $parameters, $validator) {
            $total_size = array_reduce($value, function ( $sum, $item ) {
                $sum += filesize($item->path());
                return $sum;
            });
            return $total_size < $parameters[0] * 1024;
        });

        /**
         * Publish translations
         */
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'upload');
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/upload'),
        ], 'upload-translations');

        /**
         * Publish migrations
         */
        $timestamp = date('Y_m_d_His', time());
        $this->publishes([
            __DIR__.'/../database/migrations/upload_migration.php' => $this->app->databasePath()."/migrations/{$timestamp}_upload_migration.php",
        ], 'upload-migrations');
        // php artisan vendor:publish --provider="Inewtonua\Upload\UploadServiceProvider" --tag=upload-migrations

        /**
         *  Publish config
         */
        $configPath = __DIR__ . '/../config/upload.php';
        $this->publishes([
            $configPath => config_path('upload.php')
        ], 'upload-config');

        /**
         * Publish resources
         */
        $viewPath = __DIR__.'/../resources/views';
        $this->loadViewsFrom($viewPath, 'uploads');
        $this->publishes([
            $viewPath => base_path('resources/views/vendor/uploads'),
        ], 'upload-views');

        /**
         * Удаление временных и удалённых файлов
         */
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('upload:remove')->everyFiveMinutes();
        });

    }

    public function register()
    {
        /**
         * Регистрация контракта
         */
        $this->app->bind(UploadContract::class, Upload::class);
        $this->app->bind(UploadModelContract::class, UploadModel::class);

        /**
         * Регистрация фасада
         */
        $this->app->bind('upload', function () {
            return new Upload();
        });

        /**
         * Регистрация конфигов
         */
        $configPath = __DIR__ . '/../config/upload.php';
        $this->mergeConfigFrom(
            $configPath,
            'upload'
        );

        $this->registerConsoleCommands();

    }

    protected function registerConsoleCommands()
    {
        if ($this->app->runningInConsole()) {
//            $this->app->bind('command.upload:delete-temporary', DeleteTemporaryFiles::class);
//            $this->commands([
//                'command.upload:delete-temporary',
//            ]);

            $this->commands([
                RemoveTemporaryFiles::class,
                ReGenerateImages::class
            ]);
        }
    }

    /**
     * Routing
     */
    private function registerRoutes($router) {

        /**
         * Список
         */
        $config = $this->app['config']->get('upload.route.index', []);
        //$config['namespace'] = 'Inewtonua\Upload';
        //$config['prefix'] = LaravelLocalization::setLocale();
        $router->group($config, function($router)
        {
            $router->get('/uploads', 'Inewtonua\Upload\Controllers\UploadController@index')->name('upload.index');
        });

        /**
         * Скачивание
         */
        $config = $this->app['config']->get('upload.route.download', []);
        //$config['namespace'] = 'Inewtonua\Upload';
        $router->group($config, function($router)
        {
            $router->get('/download/{id}/{filename}', 'Inewtonua\Upload\Controllers\UploadController@download')->name('download');
        });

        /**
         * Загрузка
         */
        $config = $this->app['config']->get('upload.route.upload', []);
        //$config['namespace'] = 'Inewtonua\Upload';
       // $config['prefix'] = LaravelLocalization::setLocale();
        $router->group($config, function($router)
        {
            $router->post('/upload', 'Inewtonua\Upload\Controllers\UploadController@store')->name('upload');
        });

        /**
         * Удаление
         */
        $config = $this->app['config']->get('upload.route.destroy', []);
        //$config['namespace'] = 'Inewtonua\Upload';
        //$config['prefix'] = LaravelLocalization::setLocale();
        $router->group($config, function($router)
        {
            $router->delete('/upload', 'Inewtonua\Upload\Controllers\UploadController@destroy')->name('upload.destroy');
        });

        /**
         * Поворот
         */
        $config = $this->app['config']->get('upload.route.rotate', []);
        //$config['namespace'] = 'Inewtonua\Upload';
        //$config['prefix'] = LaravelLocalization::setLocale();
        $router->group($config, function($router)
        {
            $router->post('/upload/rotate', 'Inewtonua\Upload\Controllers\UploadController@rotate')->name('upload.rotate');
        });

    }

    private function registerGates($gate) {

        $gate->define('delete-any-files', function ($user, $file) {
            return $user->hasAccess(['delete-any-files']) or $user->id == $file->user_id;
        });

        $gate->define('manage-files', function ($user) {
            return $user->hasAccess(['manage-files']);
        });

    }

}