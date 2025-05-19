<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'sylius:plugin-installer:init',
    description: 'Install Sylius plugins'
)]
class PluginOrchestratorCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                'config-file',
                InputArgument::OPTIONAL,
                'Path to booster JSON config',
                'booster.json'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->section('Plugin installation started');

        $configPath = $input->getArgument('config-file');
        if (!file_exists($configPath)) {
            $io->error("Configuration file '$configPath' not found.");
            return Command::FAILURE;
        }

        $data = json_decode(file_get_contents($configPath), true);
        if (!isset($data['plugins']) || !is_array($data['plugins'])) {
            $io->error('Invalid config: missing "plugins" array.');
            return Command::FAILURE;
        }


        $io->title('Plugins to install:');
        foreach ($data['plugins'] as $pkg => $version) {
            $io->text(" - $pkg ($version)");
        }
        $io->newLine();

        $io->info('Configuring Symfony Flex to auto-accept contrib recipes');
        Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->run();

        if (count($data['plugins']) === 0) {
            $io->success('No plugins to install.');
            return Command::SUCCESS;
        }

        $io->info('Add Sylius Packagist repository');
        Process::fromShellCommandline('composer config repositories.sylius composer https://sylius.repo.packagist.com/sylius/')->run();

        foreach ($data['plugins'] as $pkg => $version) {
            $io->info("Installing $pkg");

            $process = Process::fromShellCommandline(
                sprintf('composer require %s:%s --no-scripts --no-interaction', $pkg, $version),
            );

            $process->mustRun();

            if ($pkg === 'sylius/b2b-kit') {
                $this->rakowaInstalacjaElastica();
            }
        }

        $io->title('ELES init:');
        $io->text(Process::fromShellCommandline('ls -la config/packages')->mustRun()->getOutput());
        $io->text(Process::fromShellCommandline('cat config/packages/_sylius.yaml | head -n 10')->mustRun()->getOutput());


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
            Yaml::dump($syliusConfig)
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
            Yaml::dump($newRoutes)
        );

        $elasticConfigFile = 'config/packages/fos_elastica.yaml';
        $elasticConfig = Yaml::parseFile($elasticConfigFile);
        if (isset($elasticConfig['fos_elastica']['indexes'])) {
            unset($elasticConfig['fos_elastica']['indexes']);
        }
        file_put_contents(
            $elasticConfigFile,
            Yaml::dump($elasticConfig)
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
            Yaml::dump($routesConfig)
        );
    }
}
