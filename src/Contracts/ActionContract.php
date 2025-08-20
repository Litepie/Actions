<?php

namespace Litepie\Actions\Contracts;

interface ActionContract
{
    public function execute(): \Litepie\Actions\ActionResult;
    public function with(array $data): static;
    public function getName(): string;
    public function getData(): array;
    public function getErrors(): array;
    public function isExecuted(): bool;
}
