# Sylius Order Note Plugin

Adds one internal administrator note to each order. Administrators can create, edit
and delete notes from the order details page. Empty content removes the note.
Notes support up to 500 characters and are escaped when rendered. The plugin does
not add shop templates, API operations or serialization groups for notes.

## Requirements

PHP 8.3+ and Sylius 2.2.x. This checkout is tested with Symfony 7.4.

## Installation in a Sylius application

### 1. Install the package

Require `piotrekpilat/sylius-order-note-plugin` from a Composer repository
containing the plugin package. The package root is this directory, not the host
Sylius application's repository root. Composer registers its autoloader.

This checkout uses a Composer `path` repository pointing to
`plugins/SyliusOrderNotePlugin` and requires the package as `dev-main`.
Run `composer install` (or `composer update piotrekpilat/sylius-order-note-plugin`
after changing the package manifest). Do not add the plugin's production
namespace to the application's autoload section. The host only registers the
plugin's test namespace under `autoload-dev` to run integration tests.

### 2. Register the bundle

Add to `config/bundles.php`:

```php
SyliusOrderNotePlugin\SyliusOrderNotePlugin::class => ['all' => true],
```

The bundle loads its YAML services, resource, XML Doctrine mapping, YAML validation
and Twig Hook configuration automatically. Service definitions use YAML because
Symfony 7.4 deprecates the XML service loader.

### 3. Extend the application's configured Order model

Use `OrderNoteAwareInterface` and `OrderNoteAwareTrait`. Preserve any existing
interfaces and traits required by other plugins. The trait declares the property
and its Doctrine association, so the Order class only needs to use the trait:

```php
<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;
use SyliusOrderNotePlugin\Entity\OrderNoteAwareInterface;
use SyliusOrderNotePlugin\Entity\OrderNoteAwareTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder implements OrderNoteAwareInterface
{
    use OrderNoteAwareTrait;
}
```

For applications using XML mapping for Order, add this relation to the existing
Order mapping as well: the XML driver does not read the trait attributes.

```xml
<one-to-one field="orderNote" target-entity="SyliusOrderNotePlugin\Entity\OrderNoteInterface"
            mapped-by="order" orphan-removal="true">
    <cascade>
        <cascade-persist />
        <cascade-remove />
    </cascade>
</one-to-one>
```

The Order class must remain configured as the `sylius_order.resources.order`
model. The plugin targets the Sylius Order interface, so the application's class
name and namespace can differ from the example.

### 4. Import routes

Create `config/routes/sylius_order_note.yaml`:

```yaml
sylius_order_note_admin:
    resource: '@SyliusOrderNotePlugin/config/routes/admin.yaml'
    prefix: /admin
```

Use the same prefix as your admin firewall if your application customizes it.

### 5. Update the database

For a new installation, generate and review the application migration:

```bash
php bin/console cache:clear
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
php bin/console doctrine:schema:validate --skip-sync
```

The plugin does not ship application migrations. This repository already contains
`migrations/Version20260907120000.php`; when it has been applied, keep it and do
not generate another migration creating the same table. This refactor preserves
the existing `sylius_order_note` table and its data.

Open an order in the admin panel, save a note, reload it, and delete it to verify
the installation. Validation and CSRF protection are provided by Symfony Forms.

## Extension points

- Decorate `sylius_order_note.updater` / `OrderNoteUpdaterInterface` to customize
  note handling, or the resource factory `sylius_order_note.factory.order_note`.
- Override `sylius_resource.resources.sylius_order_note.order_note.classes.model`
  to use a subclass of the XML mapped superclass `OrderNote`.
- Use a Symfony form extension for `OrderNoteType`; both the display component
  and the POST action use the same `OrderNoteFormFactoryInterface`.
- Override the Twig Hook `sylius_admin.order.show.content.sections#right.note`.
- Translation keys and the form name use the `sylius_order_note` prefix.

## Development and verification

Run from the host application's root after installing its development
requirements and preparing its test database/schema:

```bash
composer test:order-note
composer behat:order-note
composer analyse:order-note
composer lint:order-note
```

In the supplied Docker environment, prefix each command with
`docker compose exec -T php` from the Docker project directory.

PHPUnit and Behat use dedicated Alice fixtures. Each scenario starts a transaction
and rolls it back, so it does not require a demo administrator or an existing
order. The tests use the host application's configured model classes; the unit
tests use the minimal model in `tests/Application`. Tests cover add/edit/delete,
whitespace removal, 500/501-character input, CSRF rejection and HTML escaping.

The host project's PHPUnit and PHPStan configurations also include this plugin.
The plugin's PHPStan configuration only scans its own source and tests.

For development outside this host application, run from the package root:

```bash
composer install
composer test
composer analyse
composer lint
```

The standalone PHPUnit suite covers the updater and validation without an
application kernel or database. Functional tests and Behat require an installed
Sylius test application configured as described above. Set `KERNEL_CLASS` to
that application's kernel class (the host test bootstrap loads it from its environment).

Translations belong to the plugin; the host only needs translation files when
intentionally overriding a message. Validation of the form DTO and entity uses
one shared constraint definition and `OrderNoteInterface::MAX_LENGTH`.
