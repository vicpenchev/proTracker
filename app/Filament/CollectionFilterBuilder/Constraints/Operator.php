<?php

namespace App\Filament\CollectionFilterBuilder\Constraints;

class Operator
{
    protected ?array $settings = null;
    protected ?bool $isInverse = null;

    /**
     * @param  array<string, mixed> | null  $settings
     */
    public function settings(?array $settings): static
    {
        $this->settings = $settings;

        return $this;
    }

    public function inverse(?bool $condition = true): static
    {
        $this->isInverse = $condition;

        return $this;
    }

    public function isInverse(): ?bool
    {
        return $this->isInverse;
    }

    public function getSettings(): ?array
    {
        return $this->settings;
    }
}
