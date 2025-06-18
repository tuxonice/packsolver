<?php

namespace TuxOnIce\PackSolver\Model;

/**
 * Class Package
 * Represents a Composer package with its name, version constraints, and dependencies
 */
class Package
{
    /**
     * @var string
     */
    private string $name = '';

    /**
     * @var string
     */
    private string $version = '';

    /**
     * @var string|null
     */
    private ?string $constraint = null;

    /**
     * @var string|null
     */
    private ?string $type = null;

    /**
     * @var string|null
     */
    private ?string $installedVersion = null;

    /**
     * @var string|null
     */
    private ?string $source = null;

    /**
     * @var array<string, string>
     */
    private array $dependencies = [];

    /**
     * @var array<string, string>
     */
    private array $devDependencies = [];

    /**
     * @var bool
     */
    private bool $isDev = false;

    /**
     * Package constructor
     *
     * @param string $name Optional package name
     * @param string $version Optional package version
     * @param string|null $constraint Optional version constraint
     */
    public function __construct(string $name = '', string $version = '', ?string $constraint = null)
    {
        $this->name = $name;
        $this->version = $version;
        $this->constraint = $constraint;
    }

    /**
     * Get package name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set package name
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
     * Get package version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set package version
     *
     * @param string $version
     * @return self
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Get version constraint
     *
     * @return string|null
     */
    public function getConstraint(): ?string
    {
        return $this->constraint;
    }

    /**
     * Set version constraint
     *
     * @param string|null $constraint
     * @return self
     */
    public function setConstraint(?string $constraint): self
    {
        $this->constraint = $constraint;
        return $this;
    }

    /**
     * Get package type (require or require-dev)
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set package type
     *
     * @param string|null $type
     * @return self
     */
    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get installed version
     *
     * @return string|null
     */
    public function getInstalledVersion(): ?string
    {
        return $this->installedVersion;
    }

    /**
     * Set installed version
     *
     * @param string|null $installedVersion
     * @return self
     */
    public function setInstalledVersion(?string $installedVersion): self
    {
        $this->installedVersion = $installedVersion;
        return $this;
    }

    /**
     * Get source URL
     *
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * Set source URL
     *
     * @param string|null $source
     * @return self
     */
    public function setSource(?string $source): self
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Get dependencies
     *
     * @return array<string, string>
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Set dependencies
     *
     * @param array<string, string> $dependencies
     * @return self
     */
    public function setDependencies(array $dependencies): self
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    /**
     * Add a dependency
     *
     * @param string $name
     * @param string $constraint
     * @return self
     */
    public function addDependency(string $name, string $constraint): self
    {
        $this->dependencies[$name] = $constraint;
        return $this;
    }

    /**
     * Get dev dependencies
     *
     * @return array<string, string>
     */
    public function getDevDependencies(): array
    {
        return $this->devDependencies;
    }

    /**
     * Set dev dependencies
     *
     * @param array<string, string> $devDependencies
     * @return self
     */
    public function setDevDependencies(array $devDependencies): self
    {
        $this->devDependencies = $devDependencies;
        return $this;
    }

    /**
     * Add a dev dependency
     *
     * @param string $name
     * @param string $constraint
     * @return self
     */
    public function addDevDependency(string $name, string $constraint): self
    {
        $this->devDependencies[$name] = $constraint;
        return $this;
    }

    /**
     * Check if package is a dev dependency
     *
     * @return bool
     */
    public function isDev(): bool
    {
        return $this->isDev;
    }

    /**
     * Set if package is a dev dependency
     *
     * @param bool $isDev
     * @return self
     */
    public function setIsDev(bool $isDev): self
    {
        $this->isDev = $isDev;
        return $this;
    }

    /**
 * Set version constraint (alias for setConstraint)
 *
 * @param string|null $versionConstraint
 * @return self
 */
public function setVersionConstraint(?string $versionConstraint): self
{
    return $this->setConstraint($versionConstraint);
}

/**
 * Get version constraint (alias for getConstraint)
 *
 * @return string|null
 */
public function getVersionConstraint(): ?string
{
    return $this->getConstraint();
}
}
