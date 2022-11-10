<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Sylius Standard Edition</h1>

<p align="center">This is Sylius Standard Edition repository for starting new projects.</p>

## About

Sylius is the first decoupled eCommerce framework based on [**Symfony**](http://symfony.com) and [**Doctrine**](http://doctrine-project.org). 
The highest quality of code, strong testing culture, built-in Agile (BDD) workflow and exceptional flexibility make it the best solution for application tailored to your business requirements. 
Enjoy being an eCommerce Developer again!

Powerful REST API allows for easy integrations and creating unique customer experience on any device.

We're using full-stack Behavior-Driven-Development, with [phpspec](http://phpspec.net) and [Behat](http://behat.org)

## Documentation

Documentation is available at [docs.sylius.com](http://docs.sylius.com).

## Installation

### Traditional
```bash
$ wget http://getcomposer.org/composer.phar
$ php composer.phar create-project sylius/sylius-standard project
$ cd project
$ yarn install
$ yarn build
$ php bin/console sylius:install
$ symfony serve
$ open http://localhost:8000/
```

For more detailed instruction please visit [installation chapter in our docs](https://docs.sylius.com/en/1.10/book/installation/installation.html).

### Docker

#### Development

Make sure you have installed [Docker](https://docs.docker.com/get-docker/) on your local machine.
Execute `docker compose up -d` in your favorite terminal and wait some time until the services will be ready. You might want to see docker logs.
Then enter `localhost` in your browser or execute `open localhost` in your terminal.

#### Production

The simplest way to deploy your Sylius store via Docker is via `docker-compose.prod.yml` configuration file.
To do that you need to install [Docker](https://docs.docker.com/get-docker/) on your VPS and define `MYSQL_PASSWORD` environment.
Then execute `docker compose -f docker-compose.prod.yml up -d` command in your terminal. The `MYSQL_PASSWORD` env will be automatically
applied to the rest of the config.

> When using a Virtual Private Server (VPS) we recommend having at least 2GB of RAM memory

**Quick deploy:**
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

export MYSQL_PASSWORD=SLyPJLaye7

docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec php bin/console sylius:fixtures:load --no-interaction
```

## Troubleshooting

If something goes wrong, errors & exceptions are logged at the application level:

```bash
$ tail -f var/log/prod.log
$ tail -f var/log/dev.log
```

## Contributing

Would like to help us and build the most developer-friendly eCommerce framework? Start from reading our [Contribution Guide](https://docs.sylius.com/en/latest/contributing/)!

## Stay Updated

If you want to keep up with the updates, [follow the official Sylius account on Twitter](http://twitter.com/Sylius) and [like us on Facebook](https://www.facebook.com/SyliusEcommerce/).

## Bug Tracking

If you want to report a bug or suggest an idea, please use [GitHub issues](https://github.com/Sylius/Sylius/issues).

## Community Support

Get Sylius support on [Slack](https://sylius.com/slack), [Forum](https://forum.sylius.com/) or [Stack Overflow](https://stackoverflow.com/questions/tagged/sylius).

## MIT License

Sylius is completely free and released under the [MIT License](https://github.com/Sylius/Sylius/blob/master/LICENSE).

## Authors

Sylius was originally created by [Paweł Jędrzejewski](http://pjedrzejewski.com).
See the list of [contributors from our awesome community](https://github.com/Sylius/Sylius/contributors).
