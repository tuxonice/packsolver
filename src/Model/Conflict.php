<?php

namespace TuxOnIce\PackSolver\Model;

class Conflict
{
    /**
     * @var string
     */
    private string $packageName;
    
    /**
     * @var string
     */
    private string $description;
    
    /**
     * @var array<string, array{package: string, constraint: string}>
     */
    private array $conflictingRequirements = [];
    
    /**
     * @var int
     */
    private int $severity = 1;

    /**
     * Get package name
     * 
     * @return string
     */
    public function getPackageName(): string
    {
        return $this->packageName;
    }

    /**
     * Set package name
     * 
     * @param string $packageName
     * @return self
     */
    public function setPackageName(string $packageName): self
    {
        $this->packageName = $packageName;
        return $this;
    }

    /**
     * Get conflict description
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set conflict description
     * 
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get conflicting requirements
     * 
     * @return array<string, array{package: string, constraint: string}>
     */
    public function getConflictingRequirements(): array
    {
        return $this->conflictingRequirements;
    }

    /**
     * Add a conflicting requirement
     * 
     * @param string $requiringPackage Package that requires this dependency
     * @param string $versionConstraint Version constraint required
     * @return self
     */
    public function addConflictingRequirement(string $requiringPackage, string $versionConstraint): self
    {
        $this->conflictingRequirements[$requiringPackage] = [
            'package' => $requiringPackage,
            'constraint' => $versionConstraint
        ];
        return $this;
    }

    /**
     * Set conflicting requirements
     * 
     * @param array<string, array{package: string, constraint: string}> $conflictingRequirements
     * @return self
     */
    public function setConflictingRequirements(array $conflictingRequirements): self
    {
        $this->conflictingRequirements = $conflictingRequirements;
        return $this;
    }

    /**
     * Get conflict severity (1-5, with 5 being most severe)
     * 
     * @return int
     */
    public function getSeverity(): int
    {
        return $this->severity;
    }

    /**
     * Set conflict severity
     * 
     * @param int $severity
     * @return self
     */
    public function setSeverity(int $severity): self
    {
        $this->severity = max(1, min(5, $severity));
        return $this;
    }

    /**
     * Convert conflict to array
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'packageName' => $this->packageName,
            'description' => $this->description,
            'conflictingRequirements' => $this->conflictingRequirements,
            'severity' => $this->severity,
        ];
    }
}
