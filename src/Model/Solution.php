<?php

namespace TuxOnIce\PackSolver\Model;

class Solution
{
    /**
     * @var string
     */
    private string $title;
    
    /**
     * @var string
     */
    private string $description;
    
    /**
     * @var array<string>
     */
    private array $steps = [];
    
    /**
     * @var int
     */
    private int $confidence = 1;
    
    /**
     * @var string|null
     */
    private ?string $composerCommand = null;

    /**
     * Get solution title
     * 
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set solution title
     * 
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get solution description
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set solution description
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
     * Get solution steps
     * 
     * @return array<string>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * Add a solution step
     * 
     * @param string $step
     * @return self
     */
    public function addStep(string $step): self
    {
        $this->steps[] = $step;
        return $this;
    }

    /**
     * Set solution steps
     * 
     * @param array<string> $steps
     * @return self
     */
    public function setSteps(array $steps): self
    {
        $this->steps = $steps;
        return $this;
    }

    /**
     * Get solution confidence (1-5, with 5 being highest confidence)
     * 
     * @return int
     */
    public function getConfidence(): int
    {
        return $this->confidence;
    }

    /**
     * Set solution confidence
     * 
     * @param int $confidence
     * @return self
     */
    public function setConfidence(int $confidence): self
    {
        $this->confidence = max(1, min(5, $confidence));
        return $this;
    }

    /**
     * Get composer command to execute
     * 
     * @return string|null
     */
    public function getComposerCommand(): ?string
    {
        return $this->composerCommand;
    }

    /**
     * Set composer command to execute
     * 
     * @param string|null $composerCommand
     * @return self
     */
    public function setComposerCommand(?string $composerCommand): self
    {
        $this->composerCommand = $composerCommand;
        return $this;
    }

    /**
     * Convert solution to array
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'steps' => $this->steps,
            'confidence' => $this->confidence,
            'composerCommand' => $this->composerCommand,
        ];
    }
}
