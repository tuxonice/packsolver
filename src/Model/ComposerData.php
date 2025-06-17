<?php

namespace TuxOnIce\PackSolver\Model;

class ComposerData
{
    /**
     * @var string
     */
    private string $name;
    
    /**
     * @var array<string, Package>
     */
    private array $requiredPackages = [];
    
    /**
     * @var array<string, Package>
     */
    private array $devPackages = [];
    
    /**
     * @var array<string, Package>
     */
    private array $installedPackages = [];

    /**
     * Get the project name
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the project name
     * 
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get required packages from composer.json
     * 
     * @return array<string, Package>
     */
    public function getRequiredPackages(): array
    {
        return $this->requiredPackages;
    }

    /**
     * Set required packages
     * 
     * @param array<string, Package> $requiredPackages
     * @return self
     */
    public function setRequiredPackages(array $requiredPackages): self
    {
        $this->requiredPackages = $requiredPackages;
        return $this;
    }

    /**
     * Get dev packages from composer.json
     * 
     * @return array<string, Package>
     */
    public function getDevPackages(): array
    {
        return $this->devPackages;
    }

    /**
     * Set dev packages
     * 
     * @param array<string, Package> $devPackages
     * @return self
     */
    public function setDevPackages(array $devPackages): self
    {
        $this->devPackages = $devPackages;
        return $this;
    }

    /**
     * Get installed packages from composer.lock
     * 
     * @return array<string, Package>
     */
    public function getInstalledPackages(): array
    {
        return $this->installedPackages;
    }

    /**
     * Set installed packages
     * 
     * @param array<string, Package> $installedPackages
     * @return self
     */
    public function setInstalledPackages(array $installedPackages): self
    {
        $this->installedPackages = $installedPackages;
        return $this;
    }

    /**
     * Get all packages (required, dev, and transitive)
     * 
     * @return array<string, Package>
     */
    public function getAllPackages(): array
    {
        return array_merge($this->requiredPackages, $this->devPackages, $this->installedPackages);
    }
}
