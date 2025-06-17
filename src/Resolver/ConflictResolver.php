<?php

namespace TuxOnIce\PackSolver\Resolver;

use TuxOnIce\PackSolver\Model\ComposerData;
use TuxOnIce\PackSolver\Model\Conflict;
use TuxOnIce\PackSolver\Model\Solution;
use Composer\Semver\VersionParser;
use Composer\Semver\Semver;

class ConflictResolver
{
    /**
     * @var VersionParser
     */
    private VersionParser $versionParser;
    
    /**
     * Common version ranges to try when relaxing constraints
     * 
     * @var array<string>
     */
    private array $commonVersionRanges = [
        '^1.0',
        '^2.0',
        '~1.0',
        '~2.0',
        '>=1.0',
        '>=2.0',
    ];

    public function __construct()
    {
        $this->versionParser = new VersionParser();
    }

    /**
     * Generate solutions for the detected conflicts
     * 
     * @param array<Conflict> $conflicts
     * @param ComposerData $composerData
     * @return array<Solution>
     */
    public function resolve(array $conflicts, ComposerData $composerData): array
    {
        $solutions = [];
        
        foreach ($conflicts as $conflict) {
            // Generate solutions for each conflict
            $conflictSolutions = $this->generateSolutionsForConflict($conflict, $composerData);
            $solutions = array_merge($solutions, $conflictSolutions);
        }
        
        // Sort solutions by confidence (highest first)
        usort($solutions, function (Solution $a, Solution $b) {
            return $b->getConfidence() - $a->getConfidence();
        });
        
        return $solutions;
    }
    
    /**
     * Generate solutions for a specific conflict
     * 
     * @param Conflict $conflict
     * @param ComposerData $composerData
     * @return array<Solution>
     */
    private function generateSolutionsForConflict(Conflict $conflict, ComposerData $composerData): array
    {
        $solutions = [];
        $packageName = $conflict->getPackageName();
        $requirements = $conflict->getConflictingRequirements();
        
        // Solution 1: Update the conflicting package to a newer version
        $updateSolution = $this->generateUpdateSolution($packageName, $requirements, $composerData);
        if ($updateSolution) {
            $solutions[] = $updateSolution;
        }
        
        // Solution 2: Relax version constraints
        $relaxSolution = $this->generateRelaxConstraintsSolution($packageName, $requirements, $composerData);
        if ($relaxSolution) {
            $solutions[] = $relaxSolution;
        }
        
        // Solution 3: Replace with an alternative package
        $alternativeSolution = $this->generateAlternativePackageSolution($packageName, $requirements, $composerData);
        if ($alternativeSolution) {
            $solutions[] = $alternativeSolution;
        }
        
        // Solution 4: Remove one of the conflicting dependencies
        $removeSolution = $this->generateRemoveDependencySolution($packageName, $requirements, $composerData);
        if ($removeSolution) {
            $solutions[] = $removeSolution;
        }
        
        return $solutions;
    }
    
    /**
     * Generate a solution that suggests updating the conflicting package
     * 
     * @param string $packageName
     * @param array<string, array{package: string, constraint: string}> $requirements
     * @param ComposerData $composerData
     * @return Solution|null
     */
    private function generateUpdateSolution(string $packageName, array $requirements, ComposerData $composerData): ?Solution
    {
        $solution = new Solution();
        $solution->setTitle("Update {$packageName}");
        $solution->setDescription("Update {$packageName} to a version that satisfies all requirements");
        
        // Extract version constraints
        $constraints = array_map(function ($req) {
            return $req['constraint'];
        }, $requirements);
        
        // Try to find a common version range that would satisfy all constraints
        $commonVersions = $this->findCommonVersions($constraints);
        
        if (empty($commonVersions)) {
            // No common version found, lower confidence
            $solution->setConfidence(2);
            $solution->setDescription("Try updating {$packageName} to the latest version, but this may not resolve all conflicts");
            $solution->addStep("Run: composer update {$packageName} --with-all-dependencies");
            $solution->setComposerCommand("composer update {$packageName} --with-all-dependencies");
        } else {
            // Common version found, higher confidence
            $bestVersion = $commonVersions[0];
            $solution->setConfidence(4);
            $solution->setDescription("Update {$packageName} to {$bestVersion} which should satisfy all requirements");
            $solution->addStep("Run: composer require {$packageName}:{$bestVersion} --update-with-dependencies");
            $solution->setComposerCommand("composer require {$packageName}:{$bestVersion} --update-with-dependencies");
        }
        
        return $solution;
    }
    
    /**
     * Generate a solution that suggests relaxing version constraints
     * 
     * @param string $packageName
     * @param array<string, array{package: string, constraint: string}> $requirements
     * @param ComposerData $composerData
     * @return Solution|null
     */
    private function generateRelaxConstraintsSolution(string $packageName, array $requirements, ComposerData $composerData): ?Solution
    {
        $solution = new Solution();
        $solution->setTitle("Relax version constraints");
        $solution->setDescription("Modify version constraints in composer.json to be more compatible");
        
        $directDependencies = [];
        
        // Find direct dependencies that can be modified
        foreach ($requirements as $requiringPackage => $data) {
            if (isset($composerData->getRequiredPackages()[$requiringPackage]) || 
                isset($composerData->getDevPackages()[$requiringPackage])) {
                $directDependencies[$requiringPackage] = $data['constraint'];
            }
        }
        
        if (empty($directDependencies)) {
            // No direct dependencies to modify, lower confidence
            $solution->setConfidence(1);
            $solution->addStep("No direct dependencies found to modify. You may need to fork and modify transitive dependencies.");
        } else {
            // Direct dependencies found, higher confidence
            $solution->setConfidence(3);
            
            foreach ($directDependencies as $package => $constraint) {
                // Suggest a more relaxed constraint
                $relaxedConstraint = $this->suggestRelaxedConstraint($constraint);
                
                if ($relaxedConstraint !== $constraint) {
                    $solution->addStep("In composer.json, change the requirement for {$package} from \"{$constraint}\" to \"{$relaxedConstraint}\"");
                }
            }
            
            $solution->addStep("After modifying composer.json, run: composer update");
        }
        
        return $solution;
    }
    
    /**
     * Generate a solution that suggests using an alternative package
     * 
     * @param string $packageName
     * @param array<string, array{package: string, constraint: string}> $requirements
     * @param ComposerData $composerData
     * @return Solution|null
     */
    private function generateAlternativePackageSolution(string $packageName, array $requirements, ComposerData $composerData): ?Solution
    {
        // This is a simplified implementation. In a real-world scenario,
        // you would need a database of alternative packages or use Packagist API.
        
        $solution = new Solution();
        $solution->setTitle("Consider alternative packages");
        $solution->setDescription("Replace {$packageName} with a compatible alternative");
        $solution->setConfidence(2);
        
        // Add generic steps for finding alternatives
        $solution->addStep("Search for alternative packages to {$packageName} on Packagist (https://packagist.org)");
        $solution->addStep("Check if the package is abandoned or has known compatibility issues");
        $solution->addStep("If a suitable alternative is found, replace the dependency in your composer.json");
        
        return $solution;
    }
    
    /**
     * Generate a solution that suggests removing one of the conflicting dependencies
     * 
     * @param string $packageName
     * @param array<string, array{package: string, constraint: string}> $requirements
     * @param ComposerData $composerData
     * @return Solution|null
     */
    private function generateRemoveDependencySolution(string $packageName, array $requirements, ComposerData $composerData): ?Solution
    {
        $solution = new Solution();
        $solution->setTitle("Remove conflicting dependency");
        $solution->setDescription("Remove one of the packages that requires {$packageName}");
        $solution->setConfidence(1); // Low confidence as removing dependencies can break functionality
        
        // Find direct dependencies that could potentially be removed
        $directDependencies = [];
        foreach (array_keys($requirements) as $requiringPackage) {
            if (isset($composerData->getRequiredPackages()[$requiringPackage])) {
                $directDependencies[] = $requiringPackage;
            } elseif (isset($composerData->getDevPackages()[$requiringPackage])) {
                $directDependencies[] = $requiringPackage . ' (dev)';
            }
        }
        
        if (empty($directDependencies)) {
            $solution->addStep("No direct dependencies found that could be removed. The conflict is in transitive dependencies.");
            $solution->addStep("Consider using the 'replace' property in composer.json to override transitive dependencies.");
        } else {
            $solution->addStep("Consider if you can remove any of these packages: " . implode(', ', $directDependencies));
            $solution->addStep("To remove a package, run: composer remove [package-name]");
            $solution->addStep("Warning: Removing packages may break functionality in your application");
        }
        
        return $solution;
    }
    
    /**
     * Find common versions that would satisfy all constraints
     * 
     * @param array<string> $constraints
     * @return array<string>
     */
    private function findCommonVersions(array $constraints): array
    {
        try {
            // Try some common version ranges to see if any satisfy all constraints
            $satisfyingVersions = [];
            
            foreach ($this->commonVersionRanges as $versionRange) {
                $satisfiesAll = true;
                
                foreach ($constraints as $constraint) {
                    if (!Semver::satisfies($versionRange, $constraint)) {
                        $satisfiesAll = false;
                        break;
                    }
                }
                
                if ($satisfiesAll) {
                    $satisfyingVersions[] = $versionRange;
                }
            }
            
            return $satisfyingVersions;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Suggest a more relaxed version constraint
     * 
     * @param string $constraint
     * @return string
     */
    private function suggestRelaxedConstraint(string $constraint): string
    {
        // This is a simplified implementation. In a real-world scenario,
        // you would need more sophisticated logic to relax constraints.
        
        // Convert exact version to caret constraint
        if (preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $constraint)) {
            return '^' . $constraint;
        }
        
        // Convert tilde to caret constraint
        if (strpos($constraint, '~') === 0) {
            return '^' . substr($constraint, 1);
        }
        
        // Convert strict inequality to more relaxed one
        if (strpos($constraint, '=') !== false) {
            return str_replace('=', '', $constraint);
        }
        
        // Already relaxed or can't suggest a better constraint
        return $constraint;
    }
}
