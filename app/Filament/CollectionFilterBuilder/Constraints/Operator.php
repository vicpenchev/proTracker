<?php

namespace App\Filament\CollectionFilterBuilder\Constraints;

class Operator
{
    protected ?array $settings = null;
    protected ?bool $isInverse = null;

    /**
     * Sets the settings for the object.
     *
     * @param array|null $settings The settings for the object. Pass null to remove any existing settings.
     *
     * @return static Returns an instance of the class to allow for method chaining.
     */
    public function settings(?array $settings): static
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Sets the inverse condition for the object.
     *
     * @param bool|null $condition The inverse condition. Defaults to true if not provided.
     * @return static Returns an instance of the object with the inverse condition set.
     */
    public function inverse(?bool $condition = true): static
    {
        $this->isInverse = $condition;

        return $this;
    }

    /**
     * Checks if the current object is in inverse state.
     *
     * @return bool|null Returns a boolean value indicating if the current object is in inverse state.
     *                  It returns `true` if the object is in inverse state, `false` if it is not in inverse state,
     *                  and `null` if the inverse state is unknown or not applicable.
     */
    public function isInverse(): ?bool
    {
        return $this->isInverse;
    }

    /**
     * Retrieves the settings of the object.
     *
     * @return array|null The settings array, or null if no settings are found.
     */
    public function getSettings(): ?array
    {
        return $this->settings;
    }
}
