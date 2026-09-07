# Sylius Order Note Plugin

A dedicated Sylius Plugin that introduces an administrator-only note field for orders in the Sylius eCommerce Admin Panel.

## Features
- **Admin-Only Note Field**: Assign an internal note to each order (up to 500 characters).
- **Admin Panel UI Integration**: Beautiful Tabler UI card on the Order details page (`/admin/orders/{id}`) using Sylius Twig Hooks.
- **Full CRUD Capabilities**:
  - Add or edit the note.
  - Delete the note (or leave the field empty).
  - Client-side and server-side validation (max 500 characters, CSRF protection).
- **Isolated & Secure**: Notes are strictly visible in the Admin Panel and never exposed in customer shop views or public APIs.
- **Multilingual Support**: Fully translated into English (`en`) and Polish (`pl`).
- **Comprehensive Test Suite**: Unit tests (PHPUnit) and Functional Controller tests.

---

## Installation

### 1. Register Autoloading
Add the plugin to your `composer.json`:
```json
"autoload": {
    "psr-4": {
        "SyliusOrderNotePlugin\\": "plugins/SyliusOrderNotePlugin/src/"
    }
}
```

### 2. Enable the Plugin in `config/bundles.php`
```php
return [
    // ...
    SyliusOrderNotePlugin\SyliusOrderNotePlugin::class => ['all' => true],
];
```

### 3. Implement `OrderNoteInterface` and `OrderNoteTrait` in your `Order` Entity
In `src/Entity/Order/Order.php`:
```php
<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;
use SyliusOrderNotePlugin\Entity\OrderNoteInterface;
use SyliusOrderNotePlugin\Entity\OrderNoteTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder implements OrderNoteInterface
{
    use OrderNoteTrait;
}
```

### 4. Run Database Migrations
```bash
php bin/console doctrine:migrations:migrate
```

### 5. Import Routing
In `config/routes.yaml`:
```yaml
sylius_order_note_admin:
    resource: "@SyliusOrderNotePlugin/config/routes/admin.yaml"
    prefix: /admin
```
