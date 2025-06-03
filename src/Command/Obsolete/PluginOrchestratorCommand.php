<?php

declare(strict_types=1);

namespace App\Command\Obsolete;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'sylius:plugin-installer:init',
    description: 'Install Sylius plugins'
)]
class PluginOrchestratorCommand extends Command
{
    use PluginConfigTrait;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->section('Plugin installation started');

        $io->title('Plugins to install:');
        try {
            $plugins = $this->loadPlugins($io);
        } catch (EnvNotFoundException) {
            $io->info('No plugins to process.');

            return Command::SUCCESS;
        }

        foreach ($plugins as $pkg => $version) {
            $io->text(" - $pkg ($version)");
        }
        $io->newLine();

        $io->info('Configuring Symfony Flex to auto-accept contrib recipes');
        Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->run();

        if (count($plugins) === 0) {
            $io->success('No plugins to install.');
            return Command::SUCCESS;
        }

        $io->info('Add Sylius Packagist repository');
        Process::fromShellCommandline('composer config repositories.sylius composer https://sylius.repo.packagist.com/sylius/')->run();

        foreach ($plugins as $pkg => $version) {
            $io->info("Installing $pkg");

            // Require tagged version to resolve symfony recipes correctly
            Process::fromShellCommandline(
                sprintf('composer require %s:%s --no-scripts --no-interaction', $pkg, $version),
            )->mustRun();

            // Once recipes exists - require dev-booster branch to has access custom plugin code
            Process::fromShellCommandline(
                sprintf('composer require "%s:dev-booster" --no-scripts --no-interaction', $pkg),
            )->mustRun();


            if ($pkg === 'sylius/b2b-kit') {
                $this->rakowaInstalacjaElastica();
            }
        }

        return Command::SUCCESS;
    }

    private function rakowaInstalacjaElastica(): void
    {
        // Obejście braku receptur w bitbag elasticsearch

        // 1. Import required config into config/packages/_sylius.yaml
        $syliusConfigFile = 'config/packages/_sylius.yaml';
        $syliusConfig = Yaml::parseFile($syliusConfigFile);
        if (!isset($syliusConfig['imports']) || !is_array($syliusConfig['imports'])) {
            $syliusConfig['imports'] = [];
        }
        // Prepend BitBag Elasticsearch import
        array_unshift(
            $syliusConfig['imports'],
            ['resource' => '@BitBagSyliusElasticsearchPlugin/config/config.yml']
        );
        file_put_contents(
            $syliusConfigFile,
            Yaml::dump($syliusConfig, inline: 10)
        );


        // 2. Import routing before sylius_shop in config/routes.yaml
        $shopRoutesFile = 'config/routes/sylius_shop.yaml';
        $shopRoutes = Yaml::parseFile($shopRoutesFile);
        $newRoutes = [];
        foreach ($shopRoutes as $routeName => $routeConfig) {
            if ($routeName === 'sylius_shop') {
                $newRoutes['bitbag_sylius_elasticsearch_plugin'] = [
                    'resource' => '@BitBagSyliusElasticsearchPlugin/config/routing.yml',
                ];
            }
            $newRoutes[$routeName] = $routeConfig;
        }
        file_put_contents(
            $shopRoutesFile,
            Yaml::dump($newRoutes, inline: 10)
        );

        $elasticConfigFile = 'config/packages/fos_elastica.yaml';
        $elasticConfig = Yaml::parseFile($elasticConfigFile);
        if (isset($elasticConfig['fos_elastica']['indexes'])) {
            unset($elasticConfig['fos_elastica']['indexes']);
        }
        file_put_contents(
            $elasticConfigFile,
            Yaml::dump($elasticConfig, inline: 10)
        );

        // 1. Overwrite entire ProductVariant entity with B2B-enabled version
        Process::fromShellCommandline(
            'cat > src/Entity/Product/ProductVariant.php << \'EOF\'
<?php

declare(strict_types=1);

namespace App\Entity\Product;

use BitBag\SyliusElasticsearchPlugin\Model\ProductVariantInterface as BitBagElasticsearchPluginVariant;
use BitBag\SyliusElasticsearchPlugin\Model\ProductVariantTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: \'sylius_product_variant\')]
class ProductVariant extends BaseProductVariant implements BitBagElasticsearchPluginVariant
{
    use ProductVariantTrait;

    protected function createTranslation(): ProductVariantTranslationInterface
    {
        return new ProductVariantTranslation();
    }
}
EOF'
        )->run();

        $routesFile = 'config/routes.yaml';
        $routesConfig = Yaml::parseFile($routesFile);
        if (isset($routesConfig['bitbag_sylius_elasticsearch_plugin'])) {
            unset($routesConfig['bitbag_sylius_elasticsearch_plugin']);
        }
        file_put_contents(
            $routesFile,
            Yaml::dump($routesConfig, inline: 10)
        );
    }
}
