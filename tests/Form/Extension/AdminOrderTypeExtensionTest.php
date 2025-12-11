<?php

declare(strict_types=1);

namespace App\Tests\Form\Extension;

use App\Entity\Order\Order;
use App\Form\Extension\AdminOrderTypeExtension;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\AdminBundle\Form\Type\OrderType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class AdminOrderTypeExtensionTest extends TestCase
{
    public function testBuildFormAddsAdminNotesField(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('add')
            ->with(
                'adminNotes',
                TextareaType::class,
                $this->callback(function ($options) {
                    return $options['label'] === 'sylius.order.admin_notes.label' &&
                        $options['required'] === false &&
                        $options['constraints'][0] instanceof Length &&
                        $options['constraints'][0]->max === Order::MAX_NOTE_LENGTH;
                }),
            );

        $extension = new AdminOrderTypeExtension();
        $extension->buildForm($builder, []);
    }

    public function testExtendedTypes(): void
    {
        $this->assertSame(
            [OrderType::class],
            AdminOrderTypeExtension::getExtendedTypes(),
        );
    }
}
