<?php

use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {
    putenv('ALLOW_BITBAG_OS_HEADER=0');
    $config->import('vendor/bitbag/coding-standard/ecs.php');
    $config->paths(['src', 'plugins/SyliusOrderNotePlugin/src', 'plugins/SyliusOrderNotePlugin/tests']);

    $config->ruleWithConfiguration(BinaryOperatorSpacesFixer::class, []);
    $config->ruleWithConfiguration(PhpdocSeparationFixer::class, ['groups' => [['ORM\\*']]]);
};
