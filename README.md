# Anano 2 PHP Framework

Anano 2 is a lightweight framework loosely inspired by Laravel 4, but intended to be as light and fast as possible, while still providing the most common developing aid you would expect from a full size framework.

Anano includes a DBAL/ORM (as well as built-in support for ActiveRecord, the lightest full ORM I know of), migrations, simple IOC, dynamic templating system, extensible CLI interface, as well as everything set up for Codeception testing.

## Why?

Anano was built because I needed to run many concurrent unique views on a less-than-optimal server, and Symfony and Laravel were simply too heavy. It's not nearly as well supported or thoroughly tested as better known frameworks. Do not use this for larger projects requiring long term support, unless you know exactly what you're doing.

### Features

- PSR-4 compliant autoloading, courtesy of Composer
- PDO-based DBAL/ORM
- Migrations
- RESTful controllers
- Dynamic templates using Laravel Blade-like syntax
- Middleware filters, including CSRF protection
- Input validator
- CLI interface (type `php run` in console)
- Lots of helper classes for sessions, requests, responses, passwords, etc. to make development fast and smooth.

### Limitations

Currently Anano only features automatic routing, i.e. GET /users/create binds to UsersController->getCreate().  
Something like FastRoute is probably easy enough to implement, but so far I haven't needed it myself.

In general, speed and light weight have been the focus, so if it's not something almost every project needs to use, it's simply not in there.

## Installation

```bash
git clone https://github.com/krislux/anano-2.git && cd anano-2
composer install
```

Particularly in Unix environments, you will probably want to run `sh install` as well to set up directory rights for you.

Requires Composer for autoloading only. It has no third-party dependencies.

## License

[MIT license](http://opensource.org/licenses/MIT).
