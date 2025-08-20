<?php

namespace Litepie\Actions;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Litepie\Actions\Contracts\ActionContract;
use Litepie\Actions\Events\ActionCompleted;
use Litepie\Actions\Events\ActionFailed;
use Litepie\Actions\Events\ActionStarted;
use Litepie\Actions\Exceptions\ActionException;
use Litepie\Actions\Traits\HasValidation;
use Litepie\Actions\Traits\HasCaching;
use Litepie\Actions\Traits\HasEvents;
use Litepie\Actions\Traits\HasMetrics;
use ReflectionClass;
use Throwable;

abstract class BaseAction implements ActionContract, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;
    use HasValidation, HasCaching, HasEvents, HasMetrics;

    protected ?\Illuminate\Database\Eloquent\Model $model = null;
    protected array $data = [];
    protected ?User $user = null;
    protected mixed $result = null;
    protected string $status = 'pending';
    protected array $errors = [];
    protected bool $executed = false;
    protected ?string $cacheKey = null;
    protected ?int $cacheTtl = null;
    protected bool $shouldCache = false;
    protected bool $async = false;
    protected int $retries = 3;
    protected int $timeout = 300;

    public function __construct(?\Illuminate\Database\Eloquent\Model $model = null, array $data = [], ?User $user = null)
    {
        $this->model = $model;
        $this->data = $data;
        $this->user = $user ?? Auth::user();
        $this->configure();
    }

    protected function configure(): void
    {
        $this->async = config('actions.defaults.async', false);
        $this->retries = config('actions.defaults.retries', 3);
        $this->timeout = config('actions.defaults.timeout', 300);
        $this->cacheTtl = config('actions.cache.ttl', 3600);
    }

    public static function make(?\Illuminate\Database\Eloquent\Model $model = null, array $data = [], ?User $user = null): static
    {
        return new static($model, $data, $user);
    }

    public function setModel(\Illuminate\Database\Eloquent\Model $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getModel(): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->model;
    }

    public function run(): ActionResult
    {
        if ($this->executed) {
            throw new ActionException('Action has already been executed');
        }

        $this->startMetrics();

        try {
            if ($this->shouldCache && $this->hasCachedResult()) {
                $result = $this->getCachedResult();
                $this->executed = true;
                return $result;
            }

            if (method_exists($this, 'authorize')) {
                $this->authorize();
            }

            $this->validateData();
            $this->before();
            $this->fireEvent(new ActionStarted($this, $this->data));
            $this->result = $this->handle();

            if (!$this->result instanceof ActionResult) {
                $this->result = ActionResult::success($this->result);
            }

            $this->after();

            if (method_exists($this, 'sendNotifications')) {
                $this->sendNotifications();
            }

            if ($this->shouldCache) {
                $this->cacheResult($this->result);
            }

            $this->fireEvent(new ActionCompleted($this, $this->result));
            $this->status = 'success';
            if (method_exists($this, 'logAction')) {
                $this->logAction('success', 'Action completed successfully');
            }

            $this->executed = true;
            $this->recordSuccess();

            return $this->result;

        } catch (ValidationException $e) {
            $this->errors = $e->errors();
            $result = ActionResult::failure('Validation failed', $this->errors);
            $this->fireEvent(new ActionFailed($this, $e));
            $this->recordFailure($e);
            $this->status = 'failed';
            
            if (method_exists($this, 'logAction')) {
                $this->logAction('failed', $e->getMessage());
            }
            
            throw $e;

        } catch (Throwable $e) {
            $result = ActionResult::failure($e->getMessage());
            $this->fireEvent(new ActionFailed($this, $e));
            $this->recordFailure($e);
            $this->status = 'failed';
            
            if (method_exists($this, 'logAction')) {
                $this->logAction('failed', $e->getMessage());
            }
            
            throw new ActionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function execute(): ActionResult
    {
        return $this->run();
    }

    protected function before(): void
    {
        if (method_exists($this, 'executeSubActions')) {
            $this->executeSubActions('before');
        }
    }

    abstract protected function handle(): mixed;

    protected function after(): void
    {
        if (method_exists($this, 'fireEvents')) {
            $this->fireEvents();
        }
        if (method_exists($this, 'executeSubActions')) {
            $this->executeSubActions('after');
        }
    }

    public function executeWithRetry(): ActionResult
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts < $this->retries) {
            try {
                return $this->run();
            } catch (ActionException $e) {
                $lastException = $e;
                $attempts++;
                
                if ($attempts < $this->retries) {
                    $this->executed = false;
                    $this->errors = [];
                    $this->status = 'pending';
                    sleep(pow(2, $attempts - 1));
                }
            }
        }

        throw $lastException;
    }

    public function executeAsync(): void
    {
        dispatch(function () {
            $this->run();
        });
    }

    public static function execute(?\Illuminate\Database\Eloquent\Model $model = null, array $data = [], ?User $user = null): ActionResult
    {
        return (new static($model, $data, $user))->run();
    }

    public static function runLater(\DateTimeInterface|\DateInterval|int $delay): \Illuminate\Foundation\Bus\PendingDispatch
    {
        return static::dispatch()->delay($delay);
    }

    public static function executeLater(
        \DateTimeInterface|\DateInterval|int $delay,
        ?\Illuminate\Database\Eloquent\Model $model = null,
        array $data = [],
        ?User $user = null
    ): \Illuminate\Foundation\Bus\PendingDispatch {
        return static::dispatch($model, $data, $user)->delay($delay);
    }

    public function with(array $data): static
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function cached(?string $key = null, ?int $ttl = null): static
    {
        $this->shouldCache = true;
        $this->cacheKey = $key;
        $this->cacheTtl = $ttl ?? $this->cacheTtl;
        return $this;
    }

    public function fresh(): static
    {
        $this->shouldCache = false;
        return $this;
    }

    public function retries(int $retries): static
    {
        $this->retries = $retries;
        return $this;
    }

    public function timeout(int $timeout): static
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function async(bool $async = true): static
    {
        $this->async = $async;
        return $this;
    }

    public function getName(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getResults(): array
    {
        return is_array($this->result) ? $this->result : [$this->result];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isExecuted(): bool
    {
        return $this->executed;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
