<?php

use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {
    $config->import('vendor/sylius-labs/coding-standard/ecs.php');
    $config->paths(['src']);

    $config->skip([
        VisibilityRequiredFixer::class => ['*Spec.php'],
    ]);

    $config->ruleWithConfiguration(BinaryOperatorSpacesFixer::class, []);
    $config->ruleWithConfiguration(PhpdocSeparationFixer::class, ['groups' => [['ORM\\*']]]);
};
