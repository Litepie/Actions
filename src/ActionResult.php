<?php

namespace Litepie\Actions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

class ActionResult implements Arrayable, Jsonable, JsonSerializable
{
    public function __construct(
        protected bool $success,
        protected mixed $data = null,
        protected ?string $message = null,
        protected array $errors = [],
        protected array $metadata = []
    ) {}

    public static function success(mixed $data = null, ?string $message = null, array $metadata = []): static
    {
        return new static(true, $data, $message, [], $metadata);
    }

    public static function failure(?string $message = null, array $errors = [], array $metadata = []): static
    {
        return new static(false, null, $message, $errors, $metadata);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'errors' => $this->errors,
            'metadata' => $this->metadata,
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
