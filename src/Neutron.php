<?php

namespace Neutron;

use Dotenv\Dotenv;
use Monolog\Logger;
use Twig\Environment;
use League\Route\Router;
use Psr\Log\LoggerInterface;
use League\Container\Container;
use Neutron\Database\Connection;
use Twig\Loader\FilesystemLoader;
use Monolog\Handler\StreamHandler;
use Psr\Http\Message\ResponseInterface;
use League\Container\ReflectionContainer;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Response\HtmlResponse;
use League\Route\Strategy\ApplicationStrategy;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Http\Exception\NotFoundException;

/**
 * Neutron framework core class.
 */
class Neutron
{
    protected Container $container;
    protected Router $router;

    /**
     * Neutron constructor, initializes the application components.
     */
    public function __construct()
    {
        $this->loadEnvironment();
        $this->container = new Container();
        $this->setupEnvironmentVariables();
        $this->setupLogger();
        $this->setupTemplateEngine();
        $this->setupRouter();
        $this->setupErrorHandling();

        // Set the container for the database connection
        Connection::setContainer($this->container);
    }

    /**
     * Run the application and dispatch the router.
     */
    public function run(): void
    {
        $request = ServerRequestFactory::fromGlobals();
        require __DIR__ . '/routes.php';

        try {
            $response = $this->router->dispatch($request);
        } catch (NotFoundException $e) {
            $response = $this->handle404();
        }

        $emitter = new SapiEmitter();
        $emitter->emit($response);
    }

    /**
     * Load environment variables from the .env file.
     */
    protected function loadEnvironment(): void
    {
        $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../');
        $dotenv->load();
    }

    /**
     * Store environment variables in the container for later use.
     */
    protected function setupEnvironmentVariables(): void
    {
        $this->container->add('env', [
            'db_connection' => getenv('DB_CONNECTION'),
            'db_database'   => getenv('DB_DATABASE'),
            'db_username'   => getenv('DB_USERNAME'),
            'db_password'   => getenv('DB_PASSWORD'),
            'log_channel'   => getenv('LOG_CHANNEL') ?: 'app',
            'app_debug'     => getenv('APP_DEBUG') === 'true',
        ]);
    }

    /**
     * Set up the logging system with Monolog.
     */
    protected function setupLogger(): void
    {
        $logChannel = $this->container->get('env')['log_channel'];
        $logger = new Logger($logChannel);
        $logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG));

        $this->container->add(Logger::class, $logger);
        $this->container->add(LoggerInterface::class, $logger);
    }

    /**
     * Set up the Twig templating engine.
     */
    protected function setupTemplateEngine(): void
    {
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $twig = new Environment($loader, [
            'debug' => getenv('APP_DEBUG') === 'true',
        ]);

        if (getenv('APP_DEBUG') === 'true') {
            $twig->addExtension(new \Twig\Extension\DebugExtension());
        }

        $this->container->add(Environment::class, $twig);
    }

    /**
     * Set up the router and inject the container for automatic dependency injection.
     */
    protected function setupRouter(): void
    {
        $this->router = new Router();

        // Add the ReflectionContainer to enable auto-wiring of dependencies
        $this->container->delegate(
            new ReflectionContainer()
        );

        // Set the application strategy and container
        $strategy = (new ApplicationStrategy())->setContainer($this->container);
        $this->router->setStrategy($strategy);

        // Add the router itself to the container
        $this->container->add(Router::class, $this->router);
    }

    /**
     * Handle 404 error by rendering a 404 page.
     *
     * @return ResponseInterface
     */
    protected function handle404(): ResponseInterface
    {
        $twig = $this->container->get(Environment::class);

        try {
            // Attempt to load custom 404 page if it exists
            $content = $twig->render('404.twig');
        } catch (\Twig\Error\LoaderError $e) {
            // Fallback to the default 404 page if the custom one doesn't exist
            $content = $twig->render('errors/404.twig');
        }

        return new HtmlResponse($content, 404);
    }

    /**
     * Set up error handling using Whoops.
     */
    protected function setupErrorHandling(): void
    {
        if (getenv('APP_DEBUG') === 'true') {
            $whoops = new \Whoops\Run();
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            $whoops->register();
        }
    }

    /**
     * Get the dependency container.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get the router.
     *
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}
