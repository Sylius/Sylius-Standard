<?php

use Symfony\Component\ClassLoader\DebugUniversalClassLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // Sylius bundles.
            new Sylius\Bundle\InstallerBundle\SyliusInstallerBundle(),
            new Sylius\Bundle\SalesBundle\SyliusSalesBundle(),
            new Sylius\Bundle\MoneyBundle\SyliusMoneyBundle(),
            new Sylius\Bundle\SettingsBundle\SyliusSettingsBundle(),
            new Sylius\Bundle\CartBundle\SyliusCartBundle(),
            new Sylius\Bundle\ProductBundle\SyliusProductBundle(),
            new Sylius\Bundle\VariableProductBundle\SyliusVariableProductBundle(),
            new Sylius\Bundle\TaxationBundle\SyliusTaxationBundle(),
            new Sylius\Bundle\ShippingBundle\SyliusShippingBundle(),
            new Sylius\Bundle\PaymentsBundle\SyliusPaymentsBundle(),
            new Sylius\Bundle\PromotionsBundle\SyliusPromotionsBundle(),
            new Sylius\Bundle\AddressingBundle\SyliusAddressingBundle(),
            new Sylius\Bundle\InventoryBundle\SyliusInventoryBundle(),
            new Sylius\Bundle\TaxonomiesBundle\SyliusTaxonomiesBundle(),
            new Sylius\Bundle\FlowBundle\SyliusFlowBundle(),

            new Sylius\Bundle\CoreBundle\SyliusCoreBundle(),
            new Sylius\Bundle\WebBundle\SyliusWebBundle(),
            new Sylius\Bundle\ResourceBundle\SyliusResourceBundle(),

            // Core bundles.
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),

            // Third party bundles.
            new Liip\DoctrineCacheBundle\LiipDoctrineCacheBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new FOS\RestBundle\FOSRestBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function init()
    {
        if ($this->debug) {
            ini_set('display_errors', 1);
            error_reporting(-1);

            DebugUniversalClassLoader::enable();
            ErrorHandler::register();
            if ('cli' !== php_sapi_name()) {
                ExceptionHandler::register();
            }
        } else {
            ini_set('display_errors', 0);
        }

        ini_set('date.timezone', 'UTC');
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');

        if (is_file($file = __DIR__.'/config/config_'.$this->getEnvironment().'.local.yml')) {
            $loader->load($file);
        }
    }
}
