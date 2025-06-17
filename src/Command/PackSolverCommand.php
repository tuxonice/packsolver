<?php

namespace TuxOnIce\PackSolver\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TuxOnIce\PackSolver\Analyzer\ConflictAnalyzer;
use TuxOnIce\PackSolver\Parser\ComposerFileParser;
use TuxOnIce\PackSolver\Resolver\ConflictResolver;

class PackSolverCommand extends Command
{
    protected static $defaultName = 'packsolver';
    protected static $defaultDescription = 'Analyze and solve PHP Composer dependency conflicts';

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to the project directory containing composer.json and composer.lock', getcwd())
            ->addOption('json', 'j', InputOption::VALUE_NONE, 'Output results in JSON format')
            ->addOption('verbose', 'v', InputOption::VALUE_NONE, 'Show detailed information about conflicts');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectPath = $input->getArgument('path');
        $jsonOutput = $input->getOption('json');
        $verbose = $input->getOption('verbose');

        $io->title('PackSolver - Composer Conflict Solver');

        // Validate project path
        if (!is_dir($projectPath)) {
            $io->error("Directory not found: {$projectPath}");
            return Command::FAILURE;
        }

        $composerJsonPath = $projectPath . '/composer.json';
        $composerLockPath = $projectPath . '/composer.lock';

        if (!file_exists($composerJsonPath)) {
            $io->error("composer.json not found in {$projectPath}");
            return Command::FAILURE;
        }

        if (!file_exists($composerLockPath)) {
            $io->warning("composer.lock not found in {$projectPath}. Some conflict detection features may be limited.");
        }

        $io->section('Parsing Composer files');
        $parser = new ComposerFileParser();
        
        try {
            $composerData = $parser->parse($composerJsonPath, $composerLockPath);
            $io->success('Successfully parsed Composer files');
            
            if ($verbose) {
                $io->writeln('Found ' . count($composerData->getRequiredPackages()) . ' required packages');
            }
            
            $io->section('Analyzing dependencies for conflicts');
            $analyzer = new ConflictAnalyzer();
            $conflicts = $analyzer->analyze($composerData);
            
            if (empty($conflicts)) {
                $io->success('No conflicts detected!');
                return Command::SUCCESS;
            }
            
            $io->warning(sprintf('Found %d conflict(s)', count($conflicts)));
            
            foreach ($conflicts as $index => $conflict) {
                $io->writeln(sprintf(
                    '%d. Conflict with package <info>%s</info>: %s',
                    $index + 1,
                    $conflict->getPackageName(),
                    $conflict->getDescription()
                ));
            }
            
            $io->section('Generating solutions');
            $resolver = new ConflictResolver();
            $solutions = $resolver->resolve($conflicts, $composerData);
            
            if (empty($solutions)) {
                $io->error('No solutions found for the detected conflicts');
                return Command::FAILURE;
            }
            
            $io->success(sprintf('Found %d possible solution(s)', count($solutions)));
            
            foreach ($solutions as $index => $solution) {
                $io->writeln(sprintf(
                    '%d. <info>%s</info>: %s',
                    $index + 1,
                    $solution->getTitle(),
                    $solution->getDescription()
                ));
                
                if ($verbose) {
                    $io->writeln('   Steps:');
                    foreach ($solution->getSteps() as $step) {
                        $io->writeln('   - ' . $step);
                    }
                    $io->newLine();
                }
            }
            
            if ($jsonOutput) {
                $output->writeln(json_encode([
                    'conflicts' => array_map(fn($c) => $c->toArray(), $conflicts),
                    'solutions' => array_map(fn($s) => $s->toArray(), $solutions),
                ], JSON_PRETTY_PRINT));
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            
            if ($verbose) {
                $io->writeln($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
}
