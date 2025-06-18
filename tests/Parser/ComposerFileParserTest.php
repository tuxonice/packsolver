<?php

namespace TuxOnIce\PackSolver\Tests\Parser;

use PHPUnit\Framework\TestCase;
use TuxOnIce\PackSolver\Parser\ComposerFileParser;
use TuxOnIce\PackSolver\Model\ComposerData;
use TuxOnIce\PackSolver\Exception\ParserException;

class ComposerFileParserTest extends TestCase
{
    private string $fixturesDir;
    
    protected function setUp(): void
    {
        $this->fixturesDir = dirname(__DIR__) . '/fixtures';
    }
    
    public function testParseComposerJsonOnly()
    {
        $parser = new ComposerFileParser();
        $composerJsonPath = $this->fixturesDir . '/composer.json';
        
        $composerData = $parser->parse($composerJsonPath);
        
        // Verify basic project data
        $this->assertEquals('test/project', $composerData->getName());
        
        // Verify required packages
        $requiredPackages = $composerData->getRequiredPackages();
        $this->assertCount(2, $requiredPackages);
        $this->assertArrayHasKey('symfony/console', $requiredPackages);
        $this->assertArrayHasKey('symfony/process', $requiredPackages);
        
        // Verify symfony/console package details
        $consolePackage = $requiredPackages['symfony/console'];
        $this->assertEquals('symfony/console', $consolePackage->getName());
        $this->assertEquals('^6.0', $consolePackage->getVersionConstraint());
        $this->assertEquals('require', $consolePackage->getType());
        
        // Verify dev packages
        $devPackages = $composerData->getDevPackages();
        $this->assertCount(2, $devPackages);
        $this->assertArrayHasKey('phpunit/phpunit', $devPackages);
        $this->assertArrayHasKey('squizlabs/php_codesniffer', $devPackages);
        
        // Verify phpunit package details
        $phpunitPackage = $devPackages['phpunit/phpunit'];
        $this->assertEquals('phpunit/phpunit', $phpunitPackage->getName());
        $this->assertEquals('^9.5', $phpunitPackage->getVersionConstraint());
        $this->assertEquals('require-dev', $phpunitPackage->getType());
        
        // Verify no installed packages when no lock file is provided
        $this->assertEmpty($composerData->getInstalledPackages());
    }
    
    public function testParseComposerJsonAndLock()
    {
        $parser = new ComposerFileParser();
        $composerJsonPath = $this->fixturesDir . '/composer.json';
        $composerLockPath = $this->fixturesDir . '/composer.lock';
        
        $composerData = $parser->parse($composerJsonPath, $composerLockPath);
        
        // Verify basic project data
        $this->assertEquals('test/project', $composerData->getName());
        
        // Verify required packages
        $requiredPackages = $composerData->getRequiredPackages();
        $this->assertCount(2, $requiredPackages);
        
        // Verify installed packages
        $installedPackages = $composerData->getInstalledPackages();
        $this->assertGreaterThanOrEqual(4, count($installedPackages)); // At least the 4 main packages
        
        // Verify symfony/console installed package details
        $consolePackage = $installedPackages['symfony/console'];
        $this->assertEquals('symfony/console', $consolePackage->getName());
        $this->assertEquals('6.0.19', $consolePackage->getInstalledVersion());
        $this->assertEquals('https://github.com/symfony/console.git', $consolePackage->getSource());
        $this->assertEquals('require', $consolePackage->getType());
        
        // Verify dependencies are set
        $consoleDependencies = $consolePackage->getDependencies();
        $this->assertArrayHasKey('symfony/service-contracts', $consoleDependencies);
        $this->assertArrayHasKey('symfony/string', $consoleDependencies);
        
        // Verify dev packages are also updated with installed info
        $devPackages = $composerData->getDevPackages();
        $phpunitPackage = $devPackages['phpunit/phpunit'];
        $this->assertEquals('phpunit/phpunit', $phpunitPackage->getName());
        $this->assertEquals('9.5.28', $phpunitPackage->getInstalledVersion());
        $this->assertEquals('https://github.com/sebastianbergmann/phpunit.git', $phpunitPackage->getSource());
        $this->assertEquals('require-dev', $phpunitPackage->getType());
    }
    
    public function testParseNonExistentComposerJson()
    {
        $parser = new ComposerFileParser();
        $nonExistentPath = $this->fixturesDir . '/non-existent-composer.json';
        
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("composer.json file not found at: {$nonExistentPath}");
        
        $parser->parse($nonExistentPath);
    }
    
    public function testParseInvalidComposerJson()
    {
        $invalidJsonPath = $this->fixturesDir . '/invalid-composer.json';
        
        // Create an invalid JSON file for testing
        file_put_contents($invalidJsonPath, '{invalid: json}');
        
        $parser = new ComposerFileParser();
        
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Failed to parse composer.json");
        
        try {
            $parser->parse($invalidJsonPath);
        } finally {
            // Clean up the temporary file
            if (file_exists($invalidJsonPath)) {
                unlink($invalidJsonPath);
            }
        }
    }
}
