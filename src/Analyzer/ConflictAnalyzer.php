<?php

namespace TuxOnIce\PackSolver\Analyzer;

use TuxOnIce\PackSolver\Model\ComposerData;
use TuxOnIce\PackSolver\Model\Conflict;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;

class ConflictAnalyzer
{
    /**
     * @var VersionParser
     */
    private VersionParser $versionParser;

    public function __construct()
    {
        $this->versionParser = new VersionParser();
    }

    /**
     * Analyze composer data for conflicts
     * 
     * @param ComposerData $composerData
     * @return array<Conflict>
     */
    public function analyze(ComposerData $composerData): array
    {
        $conflicts = [];
        
        // Build a dependency map to track which packages require which versions
        $dependencyMap = $this->buildDependencyMap($composerData);
        
        // Check for packages with multiple version constraints
        foreach ($dependencyMap as $packageName => $requirements) {
            if (count($requirements) <= 1) {
                continue; // No conflict if only one package requires this dependency
            }
            
            // Check if version constraints are compatible
            if ($this->hasConflictingConstraints($requirements)) {
                $conflict = new Conflict();
                $conflict->setPackageName($packageName);
                
                // Add all conflicting requirements
                foreach ($requirements as $requiringPackage => $constraint) {
                    $conflict->addConflictingRequirement($requiringPackage, $constraint);
                }
                
                // Set description based on the conflict
                $conflict->setDescription($this->generateConflictDescription($packageName, $requirements));
                
                // Set severity based on the number of conflicting packages and whether they are direct dependencies
                $conflict->setSeverity($this->calculateConflictSeverity($packageName, $requirements, $composerData));
                
                $conflicts[] = $conflict;
            }
        }
        
        // Sort conflicts by severity (highest first)
        usort($conflicts, function (Conflict $a, Conflict $b) {
            return $b->getSeverity() - $a->getSeverity();
        });
        
        return $conflicts;
    }
    
    /**
     * Build a map of package dependencies
     * 
     * @param ComposerData $composerData
     * @return array<string, array<string, string>> Map of package name => [requiring package => version constraint]
     */
    private function buildDependencyMap(ComposerData $composerData): array
    {
        $dependencyMap = [];
        
        // Add direct dependencies from composer.json
        foreach ($composerData->getRequiredPackages() as $package) {
            foreach ($package->getDependencies() as $dependencyName => $versionConstraint) {
                $dependencyMap[$dependencyName][$package->getName()] = $versionConstraint;
            }
        }
        
        // Add dev dependencies
        foreach ($composerData->getDevPackages() as $package) {
            foreach ($package->getDependencies() as $dependencyName => $versionConstraint) {
                $dependencyMap[$dependencyName][$package->getName()] = $versionConstraint;
            }
        }
        
        // Add transitive dependencies from composer.lock
        foreach ($composerData->getInstalledPackages() as $package) {
            if ($package->getType() === 'transitive' || $package->getType() === 'dev-transitive') {
                foreach ($package->getDependencies() as $dependencyName => $versionConstraint) {
                    $dependencyMap[$dependencyName][$package->getName()] = $versionConstraint;
                }
            }
        }
        
        return $dependencyMap;
    }
    
    /**
     * Check if a set of version constraints are conflicting
     * 
     * @param array<string, string> $requirements
     * @return bool
     */
    private function hasConflictingConstraints(array $requirements): bool
    {
        try {
            // Extract just the constraints
            $constraints = array_values($requirements);
            
            // Try to find a version that satisfies all constraints
            $intersection = Semver::satisfiedBy($constraints, ['1.0.0', '2.0.0', '3.0.0', '4.0.0', '5.0.0']);
            
            // If no version satisfies all constraints, we have a conflict
            return empty($intersection);
        } catch (\Exception $e) {
            // If there's an exception parsing constraints, assume there's a conflict
            return true;
        }
    }
    
    /**
     * Generate a human-readable description of the conflict
     * 
     * @param string $packageName
     * @param array<string, string> $requirements
     * @return string
     */
    private function generateConflictDescription(string $packageName, array $requirements): string
    {
        $requirementStrings = [];
        foreach ($requirements as $requiringPackage => $constraint) {
            $requirementStrings[] = "{$requiringPackage} requires {$packageName} {$constraint}";
        }
        
        return "Conflicting version constraints: " . implode(', ', $requirementStrings);
    }
    
    /**
     * Calculate the severity of a conflict
     * 
     * @param string $packageName
     * @param array<string, string> $requirements
     * @param ComposerData $composerData
     * @return int
     */
    private function calculateConflictSeverity(string $packageName, array $requirements, ComposerData $composerData): int
    {
        $severity = 1;
        
        // More conflicting requirements means higher severity
        $severity += min(2, count($requirements) - 1);
        
        // If the conflicting package is a direct dependency, increase severity
        if (isset($composerData->getRequiredPackages()[$packageName])) {
            $severity += 1;
        }
        
        // Check if any of the requiring packages are direct dependencies
        foreach (array_keys($requirements) as $requiringPackage) {
            if (isset($composerData->getRequiredPackages()[$requiringPackage])) {
                $severity += 1;
                break;
            }
        }
        
        return min(5, $severity);
    }
}
