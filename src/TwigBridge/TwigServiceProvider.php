<?php

namespace TwigBridge;

use Illuminate\View\ViewServiceProvider;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\Support\MessageBag;
use Twig_Environment;
use Twig_Lexer;

class TwigServiceProvider extends ViewServiceProvider
{
    const VERSION = '0.0.6';

    /**
     * Register the service provider.
     *
     * @param  Illuminate\Foundation\Application  $app
     * @return void
     */
    public function register()
    {
        // Register the package configuration with the loader.
        $this->app['config']->package('rcrowe/twigbridge', __DIR__.'/../config');

        $this->registerEngineResolver();
        $this->registerViewFinder();
        $this->registerEnvironment();
        $this->registerCommands();
    }

    /**
     * Register the engine resolver instance.
     *
     * @param  Illuminate\Foundation\Application  $app
     * @return void
     */
    public function registerEngineResolver()
    {
        list($me, $app) = array($this, $this->app);

        $app['view.engine.resolver'] = $app->share(function($app) use ($me)
        {
            $resolver = new EngineResolver;

            // Next we will register the various engines with the resolver so that the
            // environment can resolve the engines it needs for various views based
            // on the extension of view files. We call a method for each engines.
            foreach (array('php', 'blade', 'twig') as $engine)
            {
                $me->{'register'.ucfirst($engine).'Engine'}($resolver);
            }

            return $resolver;
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param  Illuminate\Foundation\Application  $app
     * @param  Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerTwigEngine($resolver)
    {
        $paths = $this->app['config']['view.paths'];

        // Grab the environment options from the config
        $options = $this->app['config']->get('twigbridge::environment', array());

        // If no cache path is set, we will try using the default file storage path
        if (!isset($options['cache'])) {
            $options['cache'] = $this->app['config']->get('cache.path').'/twig';
        }

        $loader = new Twig\Loader\Filesystem($paths);
        $twig   = new Twig_Environment($loader, $options);

        // Allow block delimiters to be changes
        $lexer = new Twig_Lexer($twig, $this->app['config']->get('twigbridge::delimiters', array(
            'tag_comment'  => array('{#', '#}'),
            'tag_block'    => array('{%', '%}'),
            'tag_variable' => array('{{', '}}'),
        )));

        $twig->setLexer($lexer);

        // Load config defined extensions
        $extensions = $this->app['config']->get('twigbridge::extensions', array());

        foreach ($extensions as $extension) {

            // Create a new instance of the extension
            $obj = new $extension;

            // If of correct type, set the application object on the extension
            if (get_parent_class($obj) === 'TwigBridge\Extensions\Extension') {
                $obj->setApp($this->app);
            }

            $twig->addExtension($obj);
        }

        // Alias loader
        // We look for the Twig function in our aliases
        // It takes the pattern alias_function(...)
        $aliases   = $this->app['config']->get('app.aliases', array());
        $shortcuts = $this->app['config']->get('twigbridge::alias_shortcuts', array());

        // Allow alias functions to be disabled
        if (!$this->app['config']->get('twigbridge::disable_aliases', false)) {
            $twig->registerUndefinedFunctionCallback(function($name) use($aliases, $shortcuts) {
                // Allow any method on aliased classes
                // Classes are aliased in your config/app.php file
                $alias = new Extensions\AliasLoader($aliases, $shortcuts);
                return $alias->getFunction($name);
            });
        }

        // Register twig engine
        $app = $this->app;

        $resolver->register('twig', function() use($app, $twig)
        {
            // Give anyone listening the chance to alter Twig
            // Perfect example is adding Twig extensions.
            // Another package can automatically add Twig function support.
            $app['events']->fire('twigbridge.twig', array($twig));

            return new Engines\TwigEngine($twig);
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @param  Illuminate\Foundation\Application  $app
     * @return void
     */
    public function registerViewFinder()
    {
        $this->app['view.finder'] = $this->app->share(function($app)
        {
            $paths = $app['config']['view.paths'];

            return new FileViewFinder($app['files'], $paths);
        });
    }

    /**
     * Register the view environment.
     *
     * @param  Illuminate\Foundation\Application  $app
     * @return void
     */
    public function registerEnvironment()
    {
        list($me, $app) = array($this, $this->app);

        $app['view'] = $app->share(function($app) use ($me)
        {
            // Next we need to grab the engine resolver instance that will be used by the
            // environment. The resolver will be used by an environment to get each of
            // the various engine implementations such as plain PHP or Blade engine.
            $resolver = $app['view.engine.resolver'];

            $finder = $app['view.finder'];

            $environment = new Environment($resolver, $finder, $app['events']);

            // If the current session has an "errors" variable bound to it, we will share
            // its value with all view instances so the views can easily access errors
            // without having to bind. An empty bag is set when there aren't errors.
            if ($me->sessionHasErrors($app))
            {
                $errors = $app['session']->get('errors');

                $environment->share('errors', $errors);
            }

            // Putting the errors in the view for every view allows the developer to just
            // assume that some errors are always available, which is convenient since
            // they don't have to continually run checks for the presence of errors.
            else
            {
                $environment->share('errors', new MessageBag);
            }

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
            $environment->setContainer($app);

            $environment->share('app', $app);

            return $environment;
        });
    }

    /**
     * Register the artisan commands.
     *
     * @param  Illuminate\Foundation\Application  $app
     * @return void
     */
    public function registerCommands()
    {
        // Info command
        $this->app['command.twigbridge'] = $this->app->share(function($app)
        {
            return new Console\TwigBridgeCommand;
        });

        // Empty Twig cache command
        $this->app['command.twigbridge.clean'] = $this->app->share(function($app)
        {
            return new Console\CleanCommand;
        });

        $this->commands('command.twigbridge', 'command.twigbridge.clean');
    }
}