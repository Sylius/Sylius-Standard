# Sylius Order Note Plugin

Adds one internal administrator note to each order. Administrators can create, edit
and delete notes from the order details page. Empty content removes the note.
Notes support up to 500 characters and are escaped when rendered. The plugin does
not add shop templates, API operations or serialization groups for notes.

## Requirements

PHP 8.3+ and Sylius 2.2.x. This checkout is tested with Symfony 7.4.

## Installation in a Sylius application

Run the commands below from the **host Sylius application's root directory**.
With the supplied Docker setup, run them from `sylius-docker` with the prefix
`docker compose exec -T php`, for example:

```bash
docker compose exec -T php composer install
docker compose exec -T php php bin/console cache:clear
```

### 1. Install the package

#### Local package (the setup used in this repository)

Copy the complete `plugins/SyliusOrderNotePlugin` directory into the target
application at the same path. Keep its `composer.json`, `src`, `config`,
`templates` and `translations` directories together.

Register the local package and install it:

```bash
composer config repositories.order-note '{"type":"path","url":"plugins/SyliusOrderNotePlugin","options":{"symlink":true,"versions":{"piotrekpilat/sylius-order-note-plugin":"dev-main"}}}'
composer require piotrekpilat/sylius-order-note-plugin:dev-main --no-scripts
```

In this checkout the repository and requirement are already configured, so use
`composer install --no-scripts` instead. After changing the plugin's dependency
manifest, use `composer update piotrekpilat/sylius-order-note-plugin --no-scripts`.
Scripts are postponed until the bundle and Order model are configured below.

Composer registers the plugin's production autoloader. Do not also add
`SyliusOrderNotePlugin\\` to the application's `autoload` section. The host's
`autoload-dev` entry for the plugin's test namespace is only needed to run its
integration tests, not to use the plugin.

#### Package from a Composer repository

Once the plugin is available from Packagist or a configured private Composer
repository, install its published version with:

```bash
composer require piotrekpilat/sylius-order-note-plugin --no-scripts
```

The package root must be this plugin directory. The root of
`Sylius-Standard-Note` contains the host application, so adding that Git repository
as a VCS repository does not expose the nested plugin as a separate package.

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
model. In the application's existing Sylius configuration (for example
`config/packages/_sylius.yaml`), ensure the following points to your Order class:

```yaml
sylius_order:
    resources:
        order:
            classes:
                model: App\Entity\Order\Order
```

Merge this into the existing configuration instead of defining the same YAML key
twice. The plugin targets the Sylius Order interface, so the application's class
name and namespace can differ from the example. No separate `OrderNote` entity
in the application's `src` directory is needed.

### 4. Import routes

Create `config/routes/sylius_order_note.yaml`:

```yaml
sylius_order_note_admin:
    resource: '@SyliusOrderNotePlugin/config/routes/admin.yaml'
    prefix: /admin
```

Use the same prefix as your admin firewall if your application customizes it.

### 5. Update the database

After configuring the bundle, Order model and routes, refresh the application:

```bash
composer dump-autoload
php bin/console cache:clear
```

For a **new installation in another application**, generate a migration:

```bash
php bin/console doctrine:migrations:diff
```

Review the generated migration, then apply it:

```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:schema:validate --skip-sync
```

For **this repository**, `migrations/Version20260907120000.php` already creates
the table. Run `doctrine:migrations:migrate` to apply pending migrations; do not
generate a second migration creating the same table. If it has already been
applied, no new database migration is needed for this refactor. Existing notes
remain in `sylius_order_note`.

The plugin does not ship application migrations; they belong to the host app.

### 6. Verify the installation

Check the registered route:

```bash
php bin/console debug:router sylius_order_note_admin_update
```

It should accept `POST` at `/admin/orders/{id}/note` with the default admin
prefix. Log in to the admin panel, open an order and check that:

1. The Order Note card is visible.
2. Saving a note and reloading the page preserves its content.
3. Editing changes the saved content.
4. Deleting the note or saving empty content removes it.
5. A note longer than 500 characters is rejected.

The plugin loads its own translations and Twig Hook configuration. There is no
need to copy translation files or the note template into the host application,
or to register the note hook a second time. Validation and CSRF protection are
provided by Symfony Forms.

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
