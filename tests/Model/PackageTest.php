<?php

namespace TuxOnIce\PackSolver\Tests\Model;

use PHPUnit\Framework\TestCase;
use TuxOnIce\PackSolver\Model\Package;

class PackageTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $package = new Package('vendor/package', '1.0.0', '^1.0');
        
        $this->assertEquals('vendor/package', $package->getName());
        $this->assertEquals('1.0.0', $package->getVersion());
        $this->assertEquals('^1.0', $package->getConstraint());
        $this->assertEquals('^1.0', $package->getVersionConstraint());
    }
    
    public function testSetters()
    {
        $package = new Package();
        
        $package->setName('vendor/package')
            ->setVersion('1.0.0')
            ->setConstraint('^1.0')
            ->setType('require')
            ->setInstalledVersion('1.0.5')
            ->setSource('https://github.com/vendor/package.git');
        
        $this->assertEquals('vendor/package', $package->getName());
        $this->assertEquals('1.0.0', $package->getVersion());
        $this->assertEquals('^1.0', $package->getConstraint());
        $this->assertEquals('^1.0', $package->getVersionConstraint());
        $this->assertEquals('require', $package->getType());
        $this->assertEquals('1.0.5', $package->getInstalledVersion());
        $this->assertEquals('https://github.com/vendor/package.git', $package->getSource());
    }
    
    public function testVersionConstraintAlias()
    {
        $package = new Package();
        
        $package->setVersionConstraint('^2.0');
        $this->assertEquals('^2.0', $package->getConstraint());
        $this->assertEquals('^2.0', $package->getVersionConstraint());
        
        $package->setConstraint('^3.0');
        $this->assertEquals('^3.0', $package->getConstraint());
        $this->assertEquals('^3.0', $package->getVersionConstraint());
    }
    
    public function testDependencies()
    {
        $package = new Package('vendor/package');
        
        $dependencies = [
            'vendor/dep1' => '^1.0',
            'vendor/dep2' => '^2.0'
        ];
        
        $package->setDependencies($dependencies);
        $this->assertEquals($dependencies, $package->getDependencies());
        
        $package->addDependency('vendor/dep3', '^3.0');
        $this->assertArrayHasKey('vendor/dep3', $package->getDependencies());
        $this->assertEquals('^3.0', $package->getDependencies()['vendor/dep3']);
    }
    
    public function testDevDependencies()
    {
        $package = new Package('vendor/package');
        
        $devDependencies = [
            'vendor/dev-dep1' => '^1.0',
            'vendor/dev-dep2' => '^2.0'
        ];
        
        $package->setDevDependencies($devDependencies);
        $this->assertEquals($devDependencies, $package->getDevDependencies());
        
        $package->addDevDependency('vendor/dev-dep3', '^3.0');
        $this->assertArrayHasKey('vendor/dev-dep3', $package->getDevDependencies());
        $this->assertEquals('^3.0', $package->getDevDependencies()['vendor/dev-dep3']);
    }
    
    public function testIsDev()
    {
        $package = new Package('vendor/package');
        
        $this->assertFalse($package->isDev());
        
        $package->setIsDev(true);
        $this->assertTrue($package->isDev());
        
        $package->setIsDev(false);
        $this->assertFalse($package->isDev());
    }
}
