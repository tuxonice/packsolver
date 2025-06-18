<?php

namespace TuxOnIce\PackSolver\Parser;

use TuxOnIce\PackSolver\Model\ComposerData;
use TuxOnIce\PackSolver\Model\Package;
use TuxOnIce\PackSolver\Exception\ParserException;

class ComposerFileParser
{
    /**
     * Parse composer.json and composer.lock files
     *
     * @param string $composerJsonPath Path to composer.json file
     * @param string $composerLockPath Path to composer.lock file (optional)
     * @return ComposerData Object containing parsed composer data
     * @throws ParserException If parsing fails
     */
    public function parse(string $composerJsonPath, ?string $composerLockPath = null): ComposerData
    {
        // Parse composer.json
        if (!file_exists($composerJsonPath)) {
            throw new ParserException("composer.json file not found at: {$composerJsonPath}");
        }
        
        $jsonContent = file_get_contents($composerJsonPath);
        $composerJson = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ParserException("Failed to parse composer.json: " . json_last_error_msg());
        }
        
        // Initialize ComposerData object
        $composerData = new ComposerData();
        $composerData->setName($composerJson['name'] ?? 'unknown/project');
        
        // Parse required packages from composer.json
        $requiredPackages = [];
        if (isset($composerJson['require'])) {
            foreach ($composerJson['require'] as $name => $versionConstraint) {
                // Skip php platform requirement
                if ($name === 'php') {
                    continue;
                }
                
                $package = new Package();
                $package->setName($name);
                $package->setVersionConstraint($versionConstraint);
                $package->setType('require');
                
                $requiredPackages[$name] = $package;
            }
        }
        
        // Parse dev packages from composer.json
        $devPackages = [];
        if (isset($composerJson['require-dev'])) {
            foreach ($composerJson['require-dev'] as $name => $versionConstraint) {
                $package = new Package();
                $package->setName($name);
                $package->setVersionConstraint($versionConstraint);
                $package->setType('require-dev');
                
                $devPackages[$name] = $package;
            }
        }
        
        $composerData->setRequiredPackages($requiredPackages);
        $composerData->setDevPackages($devPackages);
        
        // Parse composer.lock if available
        if ($composerLockPath && file_exists($composerLockPath)) {
            $this->parseLockFile($composerLockPath, $composerData);
        }
        
        return $composerData;
    }
    
    /**
     * Parse composer.lock file to extract installed package information
     *
     * @param string $composerLockPath Path to composer.lock file
     * @param ComposerData $composerData ComposerData object to update
     * @throws ParserException If parsing fails
     */
    private function parseLockFile(string $composerLockPath, ComposerData $composerData): void
    {
        $lockContent = file_get_contents($composerLockPath);
        $composerLock = json_decode($lockContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ParserException("Failed to parse composer.lock: " . json_last_error_msg());
        }
        
        // Parse installed packages
        $installedPackages = [];
        
        // Process packages from "packages" section (regular dependencies)
        if (isset($composerLock['packages']) && is_array($composerLock['packages'])) {
            foreach ($composerLock['packages'] as $packageData) {
                $name = $packageData['name'] ?? null;
                if (!$name) {
                    continue;
                }
                
                // Update existing package if it's in required packages
                if (isset($composerData->getRequiredPackages()[$name])) {
                    $package = $composerData->getRequiredPackages()[$name];
                } else {
                    $package = new Package();
                    $package->setName($name);
                    $package->setType('transitive');
                }
                
                $package->setInstalledVersion($packageData['version'] ?? null);
                $package->setSource($packageData['source']['url'] ?? null);
                
                // Extract dependencies of this package
                $dependencies = [];
                if (isset($packageData['require']) && is_array($packageData['require'])) {
                    foreach ($packageData['require'] as $depName => $depVersion) {
                        if ($depName !== 'php') {
                            $dependencies[$depName] = $depVersion;
                        }
                    }
                }
                $package->setDependencies($dependencies);
                
                $installedPackages[$name] = $package;
            }
        }
        
        // Process packages from "packages-dev" section (dev dependencies)
        if (isset($composerLock['packages-dev']) && is_array($composerLock['packages-dev'])) {
            foreach ($composerLock['packages-dev'] as $packageData) {
                $name = $packageData['name'] ?? null;
                if (!$name) {
                    continue;
                }
                
                // Update existing package if it's in dev packages
                if (isset($composerData->getDevPackages()[$name])) {
                    $package = $composerData->getDevPackages()[$name];
                } else {
                    $package = new Package();
                    $package->setName($name);
                    $package->setType('dev-transitive');
                }
                
                $package->setInstalledVersion($packageData['version'] ?? null);
                $package->setSource($packageData['source']['url'] ?? null);
                
                // Extract dependencies of this package
                $dependencies = [];
                if (isset($packageData['require']) && is_array($packageData['require'])) {
                    foreach ($packageData['require'] as $depName => $depVersion) {
                        if ($depName !== 'php') {
                            $dependencies[$depName] = $depVersion;
                        }
                    }
                }
                $package->setDependencies($dependencies);
                
                $installedPackages[$name] = $package;
            }
        }
        
        $composerData->setInstalledPackages($installedPackages);
    }
}
