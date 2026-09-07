<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use SyliusOrderNotePlugin\Entity\OrderNote;
use SyliusOrderNotePlugin\Form\Model\OrderNoteData;
use Symfony\Component\Validator\Validation;

final class OrderNoteValidationTest extends TestCase
{
    public function testFormDataAndEntityShareTheUnicodeLengthLimit(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->addYamlMapping(__DIR__ . '/../../../config/validation/OrderNote.yaml')
            ->getValidator();

        foreach ([null, '', str_repeat('ą', 500), str_repeat('ą', 501)] as $content) {
            $note = new OrderNote();
            $note->setNote($content);
            $expected = null !== $content && 500 < mb_strlen($content) ? 1 : 0;

            foreach ([$note, new OrderNoteData($content)] as $subject) {
                $violations = $validator->validate($subject);
                self::assertCount($expected, $violations);
                if (1 === $expected) {
                    $violation = $violations[0];
                    self::assertNotNull($violation);
                    self::assertSame('sylius_order_note.order.note.max_length', $violation->getMessageTemplate());
                }
            }
        }
    }
}
