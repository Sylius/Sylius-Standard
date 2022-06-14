<?php

use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {
    $config->import('vendor/sylius-labs/coding-standard/ecs.php');

    $config->skip([
        VisibilityRequiredFixer::class => ['*Spec.php'],
    ]);

    $config->ruleWithConfiguration(BinaryOperatorSpacesFixer::class, []);
};
