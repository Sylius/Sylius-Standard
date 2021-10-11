<?php

namespace App\Validator\CatalogPromotionScope;

use Sylius\Bundle\CoreBundle\Validator\CatalogPromotionScope\ScopeValidatorInterface;
use Sylius\Bundle\CoreBundle\Validator\Constraints\CatalogPromotionScope;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Webmozart\Assert\Assert;

class ByPhraseScopeValidator implements ScopeValidatorInterface
{
    public function validate(array $configuration, Constraint $constraint, ExecutionContextInterface $context): void
    {
        /** @var CatalogPromotionScope $constraint */
        Assert::isInstanceOf($constraint, CatalogPromotionScope::class);

        if (!array_key_exists('phrase', $configuration) || empty($configuration['phrase'])) {
            $context->buildViolation("There is no phrase provided")->atPath('configuration.phrase')->addViolation();
        }
    }
}
