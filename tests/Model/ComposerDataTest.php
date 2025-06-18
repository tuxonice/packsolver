<?php

namespace TuxOnIce\PackSolver\Tests\Model;

use PHPUnit\Framework\TestCase;
use TuxOnIce\PackSolver\Model\ComposerData;
use TuxOnIce\PackSolver\Model\Package;

class ComposerDataTest extends TestCase
{
    public function testSetAndGetName()
    {
        $composerData = new ComposerData();
        $composerData->setName('test/project');
        
        $this->assertEquals('test/project', $composerData->getName());
    }
    
    public function testSetAndGetRequiredPackages()
    {
        $composerData = new ComposerData();
        
        $package1 = new Package('vendor/package1', '1.0.0', '^1.0');
        $package1->setType('require');
        
        $package2 = new Package('vendor/package2', '2.0.0', '^2.0');
        $package2->setType('require');
        
        $requiredPackages = [
            'vendor/package1' => $package1,
            'vendor/package2' => $package2
        ];
        
        $composerData->setRequiredPackages($requiredPackages);
        
        $this->assertEquals($requiredPackages, $composerData->getRequiredPackages());
        $this->assertCount(2, $composerData->getRequiredPackages());
        $this->assertInstanceOf(Package::class, $composerData->getRequiredPackages()['vendor/package1']);
        $this->assertEquals('vendor/package1', $composerData->getRequiredPackages()['vendor/package1']->getName());
    }
    
    public function testSetAndGetDevPackages()
    {
        $composerData = new ComposerData();
        
        $package1 = new Package('vendor/dev-package1', '1.0.0', '^1.0');
        $package1->setType('require-dev');
        
        $package2 = new Package('vendor/dev-package2', '2.0.0', '^2.0');
        $package2->setType('require-dev');
        
        $devPackages = [
            'vendor/dev-package1' => $package1,
            'vendor/dev-package2' => $package2
        ];
        
        $composerData->setDevPackages($devPackages);
        
        $this->assertEquals($devPackages, $composerData->getDevPackages());
        $this->assertCount(2, $composerData->getDevPackages());
        $this->assertInstanceOf(Package::class, $composerData->getDevPackages()['vendor/dev-package1']);
        $this->assertEquals('vendor/dev-package1', $composerData->getDevPackages()['vendor/dev-package1']->getName());
    }
    
    public function testSetAndGetInstalledPackages()
    {
        $composerData = new ComposerData();
        
        $package1 = new Package('vendor/package1', '1.0.5');
        $package1->setType('require');
        $package1->setInstalledVersion('1.0.5');
        $package1->setSource('https://github.com/vendor/package1.git');
        
        $package2 = new Package('vendor/package2', '2.0.3');
        $package2->setType('require');
        $package2->setInstalledVersion('2.0.3');
        $package2->setSource('https://github.com/vendor/package2.git');
        
        $installedPackages = [
            'vendor/package1' => $package1,
            'vendor/package2' => $package2
        ];
        
        $composerData->setInstalledPackages($installedPackages);
        
        $this->assertEquals($installedPackages, $composerData->getInstalledPackages());
        $this->assertCount(2, $composerData->getInstalledPackages());
        $this->assertInstanceOf(Package::class, $composerData->getInstalledPackages()['vendor/package1']);
        $this->assertEquals('1.0.5', $composerData->getInstalledPackages()['vendor/package1']->getInstalledVersion());
        $this->assertEquals('https://github.com/vendor/package1.git', $composerData->getInstalledPackages()['vendor/package1']->getSource());
    }
    
    public function testGetAllPackages()
    {
        $composerData = new ComposerData();
        
        // Required packages
        $package1 = new Package('vendor/package1', '1.0.0', '^1.0');
        $package1->setType('require');
        
        // Dev packages
        $package2 = new Package('vendor/dev-package', '2.0.0', '^2.0');
        $package2->setType('require-dev');
        
        // Installed packages (transitive dependencies)
        $package3 = new Package('vendor/transitive', '3.0.0');
        $package3->setType('transitive');
        $package3->setInstalledVersion('3.0.0');
        
        $composerData->setRequiredPackages(['vendor/package1' => $package1]);
        $composerData->setDevPackages(['vendor/dev-package' => $package2]);
        $composerData->setInstalledPackages(['vendor/transitive' => $package3]);
        
        $allPackages = $composerData->getAllPackages();
        
        $this->assertCount(3, $allPackages);
        $this->assertArrayHasKey('vendor/package1', $allPackages);
        $this->assertArrayHasKey('vendor/dev-package', $allPackages);
        $this->assertArrayHasKey('vendor/transitive', $allPackages);
        
        // Verify the merged packages have the correct types
        $this->assertEquals('require', $allPackages['vendor/package1']->getType());
        $this->assertEquals('require-dev', $allPackages['vendor/dev-package']->getType());
        $this->assertEquals('transitive', $allPackages['vendor/transitive']->getType());
    }
    
    public function testFluentInterface()
    {
        $composerData = new ComposerData();
        
        // Test that all setters return $this for method chaining
        $returnedObject = $composerData->setName('test/project');
        $this->assertSame($composerData, $returnedObject);
        
        $returnedObject = $composerData->setRequiredPackages([]);
        $this->assertSame($composerData, $returnedObject);
        
        $returnedObject = $composerData->setDevPackages([]);
        $this->assertSame($composerData, $returnedObject);
        
        $returnedObject = $composerData->setInstalledPackages([]);
        $this->assertSame($composerData, $returnedObject);
    }
}
