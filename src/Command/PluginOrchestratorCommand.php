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
            $args = ['composer', 'require', sprintf('%s:%s', $pkg, $version)];
            $proc = new Process($args);
            $proc->mustRun();
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
        Process::fromShellCommandline(
            "sed -i '' $'/imports:/a\\\n    - { resource: \"@BitBagSyliusElasticsearchPlugin/config/config.yml\" }\\\n' config/packages/_sylius.yaml"
        )->run();

        // 2. Import routing before sylius_shop in config/routes.yaml
        Process::fromShellCommandline(
            "sed -i '' $'/sylius_shop:/i\\\nbitbag_sylius_elasticsearch_plugin:\\\n    resource: \"@BitBagSyliusElasticsearchPlugin/config/routing.yml\"\\\n' config/routes/sylius_shop.yaml"
        )->run();

        Process::fromShellCommandline(
            "sed -i '' '/^[[:space:]]*indexes:/,/^[[:space:]]*app: ~$/d' config/packages/fos_elastica.yaml"
        )->run();

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


        // 3. Remove the Elasticsearch plugin routing from config/routes.yaml
        Process::fromShellCommandline(
            "sed -i '' $'/bitbag_sylius_elasticsearch_plugin:/,+1d' config/routes.yaml"
        )->run();
    }
}
