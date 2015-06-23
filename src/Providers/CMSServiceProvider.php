<?php

namespace Gmlo\CMS\Providers;


use App\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Gmlo\CMS\CMS;
use Blade;
use Gmlo\CMS\Alert;
use Gmlo\CMS\FieldBuilder;

class CMSServiceProvider extends ServiceProvider
{

    protected $file_config;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        include __DIR__ . '/../helpers.php';

        $this->file_config = $file_config = __DIR__ . '/../config/cms.php';

        $this->mergeConfigFrom($this->file_config, 'cms');

        $this->publishFiles();

        // Translations
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'CMS');

        // Load our views
        $this->loadViewsFrom(__DIR__ . '/../views', 'CMS');
        $router->middleware('CMSAuthenticate', 'Gmlo\CMS\Middleware\CMSAuthenticate');

        $this->app['config']->set('auth.model', 'Gmlo\CMS\Modules\Users\User');

        $this->extendBlade();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__ . '/../routes.php';

        $this->app['cms'] = $this->app->share(function($app){
           return new CMS();
        });


        $this->app['alert'] = $this->app->share(function($app)
        {
            $alertBuilder = new Alert($app['view'], $app['session.store']);
            return $alertBuilder;
        });


        $this->app['field'] = $this->app->share(function($app)
        {
            $fieldBuilder = new FieldBuilder($app['form'], $app['view'], $app['session.store']);
            return $fieldBuilder;
        });

    }

    protected function publishFiles()
    {
        // Config
        $this->publishes([
            $this->file_config => base_path('config/cms.php'),
        ]);

        // Assets
        $this->publishes([
            __DIR__.'/../assets' => public_path('vendor/gmlo/cms'),
        ], 'public');

        // Migrations
        $mFrom  = __DIR__ . '/../migrations/';
        $mTo    = $this->app['path.database'] . '/migrations/';

        $this->publishes([

            $mFrom . '2015_06_15_000000_cms_create_users_table.php' => $mTo . '2015_06_15_000000_cms_create_users_table.php',

        ], 'migrations');
    }

    protected function shareGlobalVariables()
    {
        //view()->share('cms_conf', config('cms'));
    }

    protected function extendBlade()
    {
        /*Blade::directive('linkSidebarMenu', function($route, $text, $icon) {
            return "<?php echo ''; ?>";
        });*/
    }
}
