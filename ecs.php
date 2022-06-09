<?php
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->import('vendor/sylius-labs/coding-standard/ecs.php');
    $ecsConfig->parameters()->set(Option::SKIP, [
        VisibilityRequiredFixer::class => ['*Spec.php'],
    ]);
    $ecsConfig->services()->set(BinaryOperatorSpacesFixer::class)->call('configure', [[]]);
};
