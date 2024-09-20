# Neutron Framework

**Neutron** is a lightweight PHP micro-framework built with a focus on simplicity, modularity, and flexibility. It leverages modern PHP libraries for routing, templating, and logging to create a clean and efficient development experience.

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Directory Structure](#directory-structure)
- [Usage](#usage)
  - [Running the Application](#running-the-application)
  - [Routes](#routes)
  - [Controllers](#controllers)
  - [Handling Request Methods and Parameters](#handling-request-methods-and-parameters)
    - [GET Parameters](#get-parameters)
    - [POST Parameters](#post-parameters)
    - [PUT and PATCH Requests](#put-and-patch-requests)
    - [DELETE Requests](#delete-requests)
  - [Templates](#templates)
  - [Logging](#logging)
- [Working with Console Commands](#working-with-console-commands)
  - [Setting Up Console Commands](#setting-up-console-commands)
  - [Auto-Loading Commands](#auto-loading-commands)
  - [Registering a Command](#registering-a-command)
  - [Running Commands](#running-commands)
  - [Using Input Arguments and Options](#using-input-arguments-and-options)
- [Generating Migrations](#generating-migrations)
  - [Example](#example)
- [Running Migrations](#running-migrations)
- [Using the ORM](#using-the-orm)
  - [Retrieving All Records](#retrieving-all-records)
  - [Retrieving a Single Record](#retrieving-a-single-record)
  - [Filtering Records with `where()`](#filtering-records-with-where)
  - [Ordering Records with `orderBy()`](#ordering-records-with-orderby)
  - [Limiting Results with `limit()` and `offset()`](#limiting-results-with-limit-and-offset)
  - [Checking if a Record Exists with `exists()`](#checking-if-a-record-exists-with-exists)
  - [Inserting a New Record](#inserting-a-new-record)
  - [Updating an Existing Record](#updating-an-existing-record)
  - [Deleting a Record](#deleting-a-record)
  - [Building Queries without Executing Them (with `toSql()`)](#building-queries-without-executing-them-with-tosql)
- [Debugging](#debugging)
- [Environment Variables](#environment-variables)
- [Contributing](#contributing)
- [License](#license)
- [Future Improvements](#future-improvements)

---

## Features

- **Routing**: Configurable routing using `league/route`.
- **Templating**: Render templates using `twig/twig`.
- **Logging**: Powerful logging using `monolog/monolog`.
- **Environment Variables**: Manage environment configuration using `vlucas/phpdotenv`.
- **Error Handling**: Improved error pages with `filp/whoops`.
- **Dumping**: Integrated debugging with Symfony’s `var-dumper`.
- **Console**: Application Console using Symfony’s `console`.
- **Models/Migrations/ORM**: Simple Model/Migration/ORM system using PDO and basic SQL files.

---

## Requirements

- **PHP 8.2+**
- **Composer** (for dependency management)

---

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

---

## Directory Structure

```
/neutron
├── composer.json       # Composer dependencies
├── .env                # Environment configuration
├── neutron             # Console Application entry-point
├── database            # Database storage (SQLite, contents gitignored)
├── logs                # Log files (ignored by Git, but directory tracked)
├── migrations          # Database migrations
├── public              # Publicly accessible files (index.php)
├── src                 # Application source code (controllers, core framework, routes)
│   ├── Command         # Commands for handling console requests
│   ├── Console         # Console core application classes
│   ├── Controller      # Controllers for handling requests
│   ├── Database        # Database ORM classes
│   ├── Models          # Models for interacting with the ORM
│   ├── Neutron.php     # Core framework class
│   ├── console.php     # Console Router
│   └── routes.php      # HTTP Router
└── views               # Twig templates
```

---

## Usage

### Running the Application

To run the application locally, use PHP's built-in server:

```bash
php -S localhost:8000 -t public
```

Visit [http://localhost:8000](http://localhost:8000) in your browser to see the application running.

---

### Routes

Routes are defined in `src/routes.php`. Here is an example of how a route can be defined:

```php
$router->map('GET', '/home', [HomeController::class, 'index']);
```

---

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

---

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

---

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

---

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

---

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

## Templates

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

---

## Logging

The framework uses Monolog for logging. Log files are written to the `logs/` directory. The logging level and destination can be configured via the `.env` file.

Example log entry:

```php
$this->log('error', 'Something went wrong');
```

---

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

---

### Running Commands

Once the commands are defined and registered, you can run them using the `console.php` file in your project root:

```bash
php neutron hello
```

This will execute the `HelloCommand` and print the message `"Hello, World."`.

---

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

---

## Generating Migrations

You can generate migrations using the `generateMigration` command. Migrations are used to define changes to your database schema in a structured way. Optionally, you can also create a corresponding model for each migration.

### Syntax:

```bash
php neutron generateMigration <migration_name> [-m]
```

- `<migration_name>`: The name of the migration you want to create. Example: `create_messages_table`
- `-m`: (Optional) Use this flag to generate a corresponding model for the migration.

---

### Example:

```bash
php neutron generateMigration create_messages_table -m
```

This command will create:
- A migration file in the `migrations/` directory.
- A model class `Message` in the `src/Models/` directory (if `-m` is passed).

The migration file will look like this:

```sql
-- Migration: create_messages_table
-- Generated at: 2023-09-24 12:00:00

-- Write your SQL statements here
```

The model class will look like this:

```php
<?php

namespace Neutron\Models;

use Neutron\Database\Model;

class Message extends Model
{
    protected static string $table = 'messages';
}
```

---

## Running Migrations

Once you've generated migrations, you can run them using the `migrate` command. This will apply any new migrations to your database.

### Syntax:

```bash
php neutron migrate
```

This command will:
- Run all pending migrations in the `migrations/` directory.
- Automatically create the database file if you're using SQLite and it doesn't already exist.

Migrations are logged in `logs/migrations.log` for future reference.

---

## Using the ORM

Neutron provides a simple, chainable ORM for interacting with your database. Below are some examples of how you can use the ORM to interact with your data models.

---

### **Retrieving All Records**

You can retrieve all records from a database table using the `all()` method.

```php
// Get all users
$users = User::query()->all();
```

### **Retrieving a Single Record**

You can retrieve a single record based on a condition using the `where()` method combined with `one()` or `get()`.

```php
// Get a single user by email
$user = User::query()->where('email', 'email@example.com')->one();
```

Alternatively, you can use `find()` to retrieve a record by its primary key (e.g., `id`).

```php
// Find a user by ID
$user = User::query()->find(1);
```

### **Filtering Records with `where()`**

You can filter records using the `where()` method and chain multiple conditions.

```php
// Get all users with a role of 'admin'
$admins = User::query()->where('role', 'admin')->all();

// Get users where 'status' is 'active' and 'role' is 'admin'
$activeAdmins = User::query()->where('status', 'active')->where('role', 'admin')->all();
```

You can also use operators with the where query. The default, if one is not passed, is '='. An example of a "between" query is below:

```php
// Get all users who signed up between Jan 1st 2024 and Jan 31st 2024
$users = User::query()
    ->where('created_at', '>=', '2024-01-01')
    ->where('created_at', '<=', '2024-01-31')
    ->all();
```

### **Ordering Records with `orderBy()`**

You can order the results of a query using the `orderBy()` method.

```php
// Get all users ordered by 'created_at' in descending order
$users = User::query()->orderBy('created_at', 'DESC')->all();

// Get users with role 'admin', ordered by 'email' in ascending order
$admins = User::query()->where('role', 'admin')->orderBy('email', 'ASC')->all();
```

### **Limiting Results with `limit()` and `offset()`**

You can limit the number of results and skip a number of records using the `limit()` and `offset()` methods.

```php
// Get the first 5 users
$users = User::query()->limit(5)->all();

// Get users 6 to 10 (with limit and offset)
$users = User::query()->limit(5)->offset(5)->all();
```

### **Checking if a Record Exists with `exists()`**

You can check if a record exists by using the `exists()` method.

```php
// Check if a user with the specified email exists
$exists = User::query()->where('email', 'email@example.com')->exists();

if ($exists) {
    echo "User exists!";
} else {
    echo "User does not exist!";
}
```

### **Inserting a New Record**

You can create and insert a new record using the `save()` method.

```php
// Create a new user
$user = new User();
$user->email = 'newuser@example.com';
$user->password = password_hash('secret123', PASSWORD_BCRYPT);
$user->role = 'user';
$user->save();
```

After calling `save()`, the new record will be inserted into the database, and the primary key (`id`) will be automatically set on the model.

### **Updating an Existing Record**

You can update an existing record by first retrieving it and then calling `save()`.

```php
// Find a user and update their role
$user = User::query()->find(1);
$user->role = 'admin';
$user->save();
```

The `save()` method will automatically update the record in the database if the model already has a primary key.

### **Deleting a Record**

You can delete a record from the database using the `delete()` method.

```php
// Find a user and delete them
$user = User::query()->find(1);
$user->delete();
```

Once the `delete()` method is called, the record will be removed from the database, and the primary key will be unset from the model.

### **Building Queries without Executing Them (with `toSql()`)**

If you want to see the SQL query that would be executed without running it, you can use the `toSql()` method.

```php
// Generate the SQL query without executing it
$sql = User::query()->where('role', 'admin')->orderBy('created_at', 'DESC')->toSql();
echo $sql;
// Output: SELECT * FROM users WHERE role = :role ORDER BY created_at DESC
```

---

## Debugging

Enable debugging by setting `APP_DEBUG=true` in your `.env` file. This will enable error pages with detailed stack traces, powered by `Whoops`.

---

## Environment Variables

Environment variables are managed using `phpdotenv`. You can define custom environment variables in your `.env` file.

---

## Contributing

Feel free to open an issue or submit a pull request if you find any bugs or want to add new features. Contributions are always welcome!

---

## License

This project is licensed under the BSD 2-Clause "Simplified" License.

---

## Future Improvements

- **Middleware Support**: Adding middleware for request and response handling.
- **Session Management**: Integrating session handling for stateful applications.
- **Advanced Error Handling**: Configurable error handling for production environments.