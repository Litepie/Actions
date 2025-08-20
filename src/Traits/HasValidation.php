<?php

namespace Litepie\Actions\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait HasValidation
{
    protected array $rules = [];
    protected array $messages = [];
    protected array $attributes = [];

    protected function validateData(): void
    {
        $formRules = method_exists($this, 'getFormValidationRules') 
            ? $this->getFormValidationRules() 
            : [];
        $rules = array_merge($this->rules(), $formRules);
        if (empty($rules)) {
            return;
        }
        $validator = Validator::make($this->data, $rules, $this->messages(), $this->attributes());
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $this->data = $validator->validated();
    }

    protected function rules(): array
    {
        return $this->rules;
    }

    protected function messages(): array
    {
        return $this->messages;
    }

    protected function attributes(): array
    {
        return $this->attributes;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;
        return $this;
    }

    public function setMessages(array $messages): static
    {
        $this->messages = $messages;
        return $this;
    }

    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;
        return $this;
    }
}
