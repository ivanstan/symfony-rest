<?php

namespace Ivanstan\SymfonyRest\Command;

use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Doctrine\Bundle\MigrationsBundle\Command\MigrationsMigrateDoctrineCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'doctrine:reload', description: 'Purge database, execute migrations and load fixtures'
)]
final class DoctrineReloadCommand extends Command
{
    private static array $choices = [
        'y' => 'Yes',
        'n' => 'No',
    ];

    private static array $envs = [
        'dev',
        'test',
    ];

    protected static $defaultDescription = 'Reloads test database';

    protected Application $application;
    protected string $env;

    public function __construct(protected ParameterBagInterface $parameters)
    {
        parent::__construct();
        
        $this->env = strtolower((string)$parameters->get('kernel.environment'));
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Force execution even in production environment',
                false
            );
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('All data will be lost. Do you wish to continue?', self::$choices, false);
        $force = $input->getOption('force') !== false;

        if (!$force && !\in_array(strtolower($this->env), self::$envs, true)) {
            $io->warning(
                'This is intended for use only in dev or test environment. Run with -f parameter to execute regardless of environment.'
            );

            return Command::FAILURE;
        }

        if ($input->getOption('no-interaction') || $helper->ask($input, $output, $question) === 'y') {
            $this->setupApplication();

            $io->writeln('Drop database');
            $this->call('doctrine:database:drop', ['--force' => true]);

            $io->writeln('Create database');
            $this->call('doctrine:database:create', ['--if-not-exists' => true]);

            if (class_exists(MigrateCommand::class)) {
                $io->writeln('Execute migrations');
                $this->call('doctrine:migrations:migrate', ['--no-interaction' => true]);
            }

            if (class_exists(LoadDataFixturesDoctrineCommand::class)) {
                $io->writeln('Load fixtures');
                $this->call('doctrine:fixtures:load', ['--no-interaction' => true]);
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @throws \Exception
     */
    protected function call(string $command, array $arguments): int
    {
        return $this->application->run(
            new ArrayInput(array_merge($arguments, ['command' => $command]))
        );
    }

    protected function setupApplication(): void
    {
        $application = $this->getApplication();

        if ($application === null) {
            throw new \RuntimeException('Application instance not found.');
        }

        $this->application = $application;

        $this->application->setAutoExit(false);
    }
}
