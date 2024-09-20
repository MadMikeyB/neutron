# Neutron Framework

**Neutron** is a lightweight PHP micro-framework built with a focus on simplicity, modularity, and flexibility. It leverages modern PHP libraries for routing, templating, and logging to create a clean and efficient development experience.

## Features

- **Routing**: Configurable routing using `league/route`.
- **Templating**: Render templates using `twig/twig`.
- **Logging**: Powerful logging using `monolog/monolog`.
- **Environment Variables**: Manage environment configuration using `vlucas/phpdotenv`.
- **Error Handling**: Improved error pages with `filp/whoops`.
- **Dumping**: Integrated debugging with Symfony’s `var-dumper`.
- **Console**: Application Console using Symfony’s `console`.

## Requirements

- **PHP 8.2+**
- **Composer** (for dependency management)

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/madmikeyb/neutron.git
   ```

2. Navigate into the project directory:

   ```bash
   cd neutron
   ```

3. Install dependencies using Composer:

   ```bash
   composer install
   ```

4. Set up your environment variables:

   Copy the example `.env` file to set up environment variables:

   ```bash
   cp .env.example .env
   ```

5. Update your `.env` file with the necessary configuration:

   ```env
   APP_ENV=development
   APP_DEBUG=true
   LOG_CHANNEL=app
   ```

## Directory Structure

```
/neutron
    /public            # Publicly accessible files (index.php)
    /src               # Application source code (controllers, core framework, routes)
        /Controller    # Controllers for handling requests
        /Command       # Commands for handling console requests
        Neutron.php    # Core framework class
        routes.php     # Router file
        console.php    # Console router
    /views             # Twig templates
    /logs              # Log files (ignored by Git, but directory tracked)
    .env               # Environment configuration
    composer.json      # Composer dependencies
```

## Usage

### Running the Application

To run the application locally, use PHP's built-in server:

```bash
php -S localhost:8000 -t public
```

Visit [http://localhost:8000](http://localhost:8000) in your browser to see the application running.

### Routes

Routes are defined in `src/routes.php`. Here is an example of how a route can be defined:

```php
$router->map('GET', '/home', [HomeController::class, 'index']);
```

### Controllers

Controllers are located in the `src/Controller` directory. A typical controller extends the `BaseController` and uses dependency injection for services like logging and rendering.

Example:

```php
<?php

namespace Neutron\Controller;

use Psr\Http\Message\ResponseInterface;

class HomeController extends BaseController
{
    public function index(): ResponseInterface
    {
        $this->log('info', 'Home page accessed');
        return $this->view('home.twig', ['name' => 'Mikey']);
    }
}
```

## Handling Request Methods and Parameters

### GET Parameters

To retrieve query parameters (GET) in your controller, use the `getQueryParams()` method from the `ServerRequestInterface`. This method returns an associative array of all query parameters.

Example:

```php
public function index(ServerRequestInterface $request): ResponseInterface
{
    // Get query parameters from the URL
    $queryParams = $request->getQueryParams();

    // Access specific query parameter 'foo'
    $foo = $queryParams['foo'] ?? null;

    // Further processing...
}
```

If you visit `https://example.com/home?foo=bar`, the `$foo` variable will contain `"bar"`.

### POST Parameters

For POST requests (typically form submissions), use the `getParsedBody()` method. This method returns an associative array of all parameters sent in the body of the request.

Example:

```php
public function create(ServerRequestInterface $request): ResponseInterface
{
    // Get POST parameters from the request body
    $postParams = $request->getParsedBody();

    // Access specific POST parameter 'foo'
    $foo = $postParams['foo'] ?? null;

    // Further processing...
}
```

If a form submits a POST request to `https://example.com/create` with a field `foo` having the value `bar`, the `$foo` variable will contain `"bar"`.

### PUT and PATCH Requests

Both PUT and PATCH requests are used to update resources. You can retrieve the data from these methods using `getParsedBody()` just like with POST.

Example:

```php
public function update(ServerRequestInterface $request): ResponseInterface
{
    // Get PUT/PATCH parameters from the request body
    $putParams = $request->getParsedBody();

    // Access specific parameter 'foo'
    $foo = $putParams['foo'] ?? null;

    // Further processing...
}
```

A PUT request to `https://example.com/update/1` containing data like `foo=bar` will allow you to access the `foo` value via `$foo`.

### DELETE Requests

While DELETE requests typically do not carry data, if they do, you can use `getParsedBody()` to retrieve body data or `getQueryParams()` for URL query parameters.

Example:

```php
public function delete(ServerRequestInterface $request): ResponseInterface
{
    // Access query parameters (optional)
    $queryParams = $request->getQueryParams();
    $id = $queryParams['id'] ?? null;

    // Optionally retrieve data from the body of the request
    $bodyParams = $request->getParsedBody();
    $confirmation = $bodyParams['confirm'] ?? null;

    // Further processing...
}
```

For example, a DELETE request to `https://example.com/delete?id=1` would allow you to access the `id` query parameter.

---

### Does This Also Support PUT, PATCH, and DELETE Methods?

Yes, the framework fully supports PUT, PATCH, and DELETE methods. These HTTP methods can be mapped to specific routes in your routing configuration, just like GET and POST. For example:

```php
// Mapping a PUT request to the 'update' method
$router->map('PUT', '/update/{id}', [YourController::class, 'update']);

// Mapping a DELETE request to the 'delete' method
$router->map('DELETE', '/delete/{id}', [YourController::class, 'delete']);
```

In these cases, you can retrieve data from the request body (for PUT, PATCH) or query parameters (for DELETE) using the `getParsedBody()` or `getQueryParams()` methods.

### Templates

Twig templates are located in the `views` directory. Here's an example `home.twig` template:

```twig
<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Neutron Framework</title>
</head>
<body>
    <h1>Hello {{ name }}!</h1>
</body>
</html>
```

### Logging

The framework uses Monolog for logging. Log files are written to the `logs/` directory. The logging level and destination can be configured via the `.env` file.

Example log entry:

```php
$this->log('error', 'Something went wrong');
```

## Working with Console Commands

Neutron supports command-line operations using Symfony Console, which allows you to create and run CLI commands easily.

### Setting Up Console Commands

Neutron provides a `console.php` file that acts as an entry point for the command-line application. You can register and manage your custom commands in the `src/console.php` file.

### Auto-Loading Commands

Neutron also supports automatic loading of commands from the `src/Command/` directory. This allows you to define commands without manually registering them in the `src/console.php` file.

To take advantage of this, simply add your command classes to the `src/Command/` directory, and they will be automatically loaded and registered by the framework.

### Registering a Command

To add a new command to your application, follow these steps:

1. **Create a Command Class**: Define a new command class in the `src/Command/` directory by extending `Symfony\Component\Console\Command\Command`.

   #### Example Command:

   ```php
   <?php
   namespace Neutron\Command;

   use Symfony\Component\Console\Command\Command;
   use Symfony\Component\Console\Input\InputInterface;
   use Symfony\Component\Console\Output\OutputInterface;

   class HelloCommand extends Command
   {
       protected static $defaultName = 'hello';

       protected function configure(): void
       {
           // Set the command name and description
           $this->setName(self::$defaultName)
                ->setDescription('Hello Command')
                ->setHelp('This command prints "Hello, World".');
       }

       protected function execute(InputInterface $input, OutputInterface $output): int
       {
           $output->writeln('Hello, World.');

           return Command::SUCCESS;
       }
   }
   ```

2. **(Optional) Register the Command**: Open the `src/console.php` file and register the command:

   ```php
   <?php

   use Neutron\Command\HelloCommand;

   // Register the HelloCommand
   $application->add(new HelloCommand());
   ```

### Running Commands

Once the commands are defined and registered, you can run them using the `console.php` file in your project root:

```bash
php neutron hello
```

This will execute the `HelloCommand` and print the message `"Hello, World."`.

### Using Input Arguments and Options

Symfony Console also supports input arguments and options for more flexible commands.

#### Example with Arguments:

```php
protected function configure(): void
{
    $this
        ->setName('greet')
        ->setDescription('Greets a person')
        ->addArgument('name', InputArgument::REQUIRED, 'The name of the person.')
        ->setHelp('This command allows you to greet someone.');
}

protected function execute(InputInterface $input, OutputInterface $output): int
{
    $name = $input->getArgument('name');
    $output->writeln('Hello, ' . $name);

    return Command::SUCCESS;
}
```

You can now run the command with an argument:

```bash
php neutron greet Mikey
```

### Debugging

Enable debugging by setting `APP_DEBUG=true` in your `.env` file. This will enable error pages with detailed stack traces, powered by `Whoops`.

### Environment Variables

Environment variables are managed using `phpdotenv`. You can define custom environment variables in your `.env` file.

## Contributing

Feel free to open an issue or submit a pull request if you find any bugs or want to add new features. Contributions are always welcome!

## License

This project is licensed under the BSD 2-Clause "Simplified" License.

---

## Future Improvements

- **Middleware Support**: Adding middleware for request and response handling.
- **Session Management**: Integrating session handling for stateful applications.
- **Advanced Error Handling**: Configurable error handling for production environments.

