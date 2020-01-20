<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Command;

use function Safe\sprintf;
use Setono\SyliusPickupPointPlugin\Message\Command\LoadPickupPoints;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

final class LoadPickupPointsCommand extends Command
{
    protected static $defaultName = 'setono-sylius-pickup-point:load-pickup-points';

    /** @var SymfonyStyle */
    private $io;

    /** @var ServiceRegistryInterface */
    private $providerRegistry;

    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(ServiceRegistryInterface $providerRegistry, MessageBusInterface $messageBus)
    {
        $this->providerRegistry = $providerRegistry;
        $this->messageBus = $messageBus;

        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Load all pickup points into local database')
            ->addArgument('provider', InputArgument::OPTIONAL, 'If given, the command will only fetch pickup points from this provider')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $providerCode = $input->getArgument('provider');

        if (is_string($providerCode)) {
            $providers = [$this->providerRegistry->get($providerCode)];
        } else {
            $providers = $this->providerRegistry->all();
        }

        $this->dispatch($providers);

        return 0;
    }

    private function dispatch(array $providers): void
    {
        ProgressBar::setFormatDefinition('custom', ' %current%/%max%: %message%');

        $progressBar = $this->io->createProgressBar(count($providers));
        $progressBar->setFormat('custom');

        foreach ($providers as $provider) {
            $progressBar->setMessage(sprintf('Dispatching command to load pickup points for %s', (string) $provider));
            $this->messageBus->dispatch(new LoadPickupPoints($provider));
            $progressBar->advance();
        }
        $progressBar->finish();

        $this->io->newLine();
        $this->io->success('All commands dispatched!');
    }
}
