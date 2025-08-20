<?php

namespace Litepie\Actions\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeActionCommand extends GeneratorCommand
{
    protected $signature = 'make:action {name : The name of the action}
                            {--validation : Add validation rules}
                            {--cacheable : Make the action cacheable}
                            {--async : Make the action async}';

    protected $description = 'Create a new action class';
    protected $type = 'Action';

    protected function getStub(): string
    {
        if ($this->option('validation')) {
            return __DIR__ . '/stubs/action.validation.stub';
        }
        return __DIR__ . '/stubs/action.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Actions';
    }

    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());
        return $this->replaceNamespace($stub, $name)
            ->replaceClass($stub, $name);
    }

    protected function replaceClass($stub, $name): string
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        $replace = [
            '{{ class }}' => $class,
            '{{class}}' => $class,
            '{{ cacheable }}' => $this->option('cacheable') ? 'true' : 'false',
            '{{ async }}' => $this->option('async') ? 'true' : 'false',
        ];
        return str_replace(array_keys($replace), array_values($replace), $stub);
    }
}
