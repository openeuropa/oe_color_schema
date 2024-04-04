# OpenEuropa Color Scheme
Contains a field that allows to choose between color schemes defined in the default theme.

## Requirements

This depends on the following software:

* [PHP >= 8.1](http://php.net/)

## Installation

The recommended way of installing the OpenEuropa Color Scheme is via [Composer][1].

```bash
composer require openeuropa/oe_color_scheme
```

before enabling the module, add a list of color scheme options to your default theme:

```yaml
color_scheme:
  - scheme_name_1
  - scheme_name_2
```

The option labels will be generated from the machine names, so `scheme_name_1` will be transformed into label "Scheme name 1".
After defining the option list enable the module as any other drupal module.

```bash
drush en oe_color_scheme
```

## Development

The OpenEuropa Color Scheme project contains all the necessary code and tools for an effective development process.
### Project setup

You can build the test site by running the following steps.

* Install all the composer dependencies:

```bash
composer install
```

* Customize build settings by copying `runner.yml.dist` to `runner.yml` and changing relevant values, like your database credentials.

This will also symlink the module in the proper directory within the test site.

* Install test site by running:

```bash
./vendor/bin/run drupal:site-install
```

Your test site will be available at `./build`.

**Please note:** project files and directories are symlinked within the test site by using the
[OpenEuropa Task Runner's Drupal project symlink](https://github.com/openeuropa/task-runner-drupal-project-symlink) command.

If you add a new file or directory in the root of the project, you need to re-run `drupal:site-setup` in order to make
sure they are be correctly symlinked.

If you don't want to re-run a full site setup for that, you can simply run:

```
$ ./vendor/bin/run drupal:symlink-project
```

### Using Docker Compose

Docker provides the necessary services and tools such as a web server and a database server to get the site running, regardless of your local host configuration.

#### Requirements:

- [Docker](https://www.docker.com/get-docker)
- [Docker Compose](https://docs.docker.com/compose/)

#### Configuration

By default, Docker Compose reads two files, a `docker-compose.yml` and an optional `docker-compose.override.yml` file.
By convention, the `docker-compose.yml` contains your base configuration, and it's provided by default.
The override file, as its name implies, can contain configuration overrides for existing services or entirely new services.
If a service is defined in both files, Docker Compose merges the configurations.

Find more information on Docker Compose extension mechanism on [the official Docker Compose documentation](https://docs.docker.com/compose/extends/).

#### Usage

To start, run:

```bash
docker-compose up
```

It's advised to not daemonize `docker-compose` so you can turn it off (`CTRL+C`) quickly when you're done working.
However, if you'd like to daemonize it, you have to add the flag `-d`:

```bash
docker-compose up -d
```

Then:

```bash
docker-compose exec web composer install
docker-compose exec web ./vendor/bin/run drupal:site-install
```

Using default configuration, the development site files should be available in the `build` directory and the development site
should be available at: [http://127.0.0.1:8080/build](http://127.0.0.1:8080/build).

#### Running the tests

To run the grumphp checks:

```bash
docker-compose exec web ./vendor/bin/grumphp run
```

To run the phpunit tests:

```bash
docker-compose exec web ./vendor/bin/phpunit
```

#### Step debugging

To enable step debugging from the command line, pass the `XDEBUG_SESSION` environment variable with any value to the container:

```bash
docker-compose exec -e XDEBUG_SESSION=1 web <your command>
```

Please note that, starting from XDebug 3, a connection error message will be outputted in the console if the variable is
set but your client is not listening for debugging connections. The error message will cause false negatives for PHPUnit
tests.

To initiate step debugging from the browser, set the correct cookie using a browser extension or a bookmarklet
like the ones generated at https://www.jetbrains.com/phpstorm/marklets/.

## Contributing

Please read [the full documentation](https://github.com/openeuropa/openeuropa) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the available versions, see the [tags on this repository](https://github.com/openeuropa/oe_content/tags).

[1]: https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies#managing-contributed
