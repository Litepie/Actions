<?php

namespace Litepie\Actions\Traits;

/**
 * Handles form generation and management for actions
 */
trait ManagesForms
{
    /**
     * Check if this action requires a form for user input
     */
    public function requiresForm(): bool
    {
        return !empty($this->getFormFields());
    }

    /**
     * Get the form configuration for this action
     */
    public function getFormFields(): array
    {
        return [];
    }

    /**
     * Get form validation rules specifically for form fields
     * This is separate from the main validation to handle form-specific validation
     */
    public function getFormValidationRules(): array
    {
        return [];
    }

    /**
     * Render the form for this action (returns form configuration)
     */
    public function renderForm(): array
    {
        return [
            'action' => static::class,
            'title' => $this->getFormTitle(),
            'description' => $this->getFormDescription(),
            'fields' => $this->getFormFields(),
            'submit_text' => $this->getFormSubmitText(),
            'cancel_url' => $this->getFormCancelUrl(),
        ];
    }

    /**
     * Get the form title
     */
    protected function getFormTitle(): string
    {
        return 'Action Form';
    }

    /**
     * Get the form description
     */
    protected function getFormDescription(): string
    {
        return 'Please fill out the form below to proceed with this action.';
    }

    /**
     * Get the form submit button text
     */
    protected function getFormSubmitText(): string
    {
        return 'Submit';
    }

    /**
     * Get the cancel URL for the form
     */
    protected function getFormCancelUrl(): ?string
    {
        return null;
    }
}
