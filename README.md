![Sylius](https://dl.dropboxusercontent.com/u/46579820/sylius-logo.jpg)

[![Gitter chat](https://badges.gitter.im/Sylius/Sylius.png)](https://gitter.im/Sylius/Sylius)
[![License](https://img.shields.io/packagist/l/Sylius/Sylius.svg)](https://packagist.org/packages/sylius/sylius)

Sylius is an open source e-commerce solution for **PHP**, based on the [**Symfony2**](http://symfony.com) framework.

Ultimate goal of the project is to create a webshop engine, which is user-friendly, *loved* by developers and has a helpful community.

Sylius is constructed from fully decoupled components (bundles in Symfony2 glossary), which means that every feature (products catalog, shipping engine, promotions system...) can be used in any other application. 

We're using full-stack BDD methodology, with [phpspec](http://phpspec.net) and [Behat](http://behat.org).

This package contains the Sylius Standard Edition, which serves as a foundation for your custom apps.

Installation
------------

``` bash
$ wget http://getcomposer.org/composer.phar
$ php composer.phar create-project sylius/sylius-standard path/to/install
$ cd path/to/install
$ php app/console sylius:install
```

Contributing
------------

All informations about contributing to Sylius can be found on [this page](http://docs.sylius.org/en/latest/contributing/index.html).

Sylius on Twitter
-----------------

If you want to keep up with the updates, [follow the official Sylius account on twitter](http://twitter.com/Sylius).

MIT License
-----------

License can be found [here](https://github.com/Sylius/Sylius/blob/master/LICENSE).

Authors
-------

Sylius was originally created by [Paweł Jędrzejewski](http://pjedrzejewski.com).
See the list of [contributors](https://github.com/Sylius/Sylius/contributors).
