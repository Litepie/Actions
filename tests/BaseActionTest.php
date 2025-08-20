<?php

namespace Litepie\Actions\Tests;

use Litepie\Actions\BaseAction;
use Litepie\Actions\ActionResult;
use Litepie\Actions\Exceptions\ActionException;
use Orchestra\Testbench\TestCase;
use Illuminate\Validation\ValidationException;

class BaseActionTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            \Litepie\Actions\ActionsServiceProvider::class,
        ];
    }

    /** @test */
    public function it_can_execute_an_action()
    {
        $action = new TestAction(['name' => 'test']);
        $result = $action->execute();
        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(['processed' => 'test'], $result->getData());
    }

    /** @test */
    public function it_throws_exception_when_executed_twice()
    {
        $action = new TestAction(['name' => 'test']);
        $action->execute();
        $this->expectException(ActionException::class);
        $action->execute();
    }

    /** @test */
    public function it_validates_input_data()
    {
        $action = new ValidationTestAction([]);
        $this->expectException(ValidationException::class);
        $action->execute();
    }

    /** @test */
    public function it_can_cache_results()
    {
        $action = new CacheableTestAction(['name' => 'test'])
            ->cached('test-key', 60);
        $result1 = $action->execute();
        $action2 = new CacheableTestAction(['name' => 'test'])
            ->cached('test-key', 60);
        $result2 = $action2->execute();
        $this->assertEquals($result1->getData(), $result2->getData());
    }

    /** @test */
    public function it_can_retry_failed_actions()
    {
        $action = new RetryableTestAction(['fail_times' => 2])
            ->retries(3);
        $result = $action->executeWithRetry();
        $this->assertTrue($result->isSuccess());
    }

    /** @test */
    public function it_collects_metrics()
    {
        $action = new TestAction(['name' => 'test']);
        $action->execute();
        $metrics = $action->getMetrics();
        $this->assertArrayHasKey('execution_time', $metrics);
        $this->assertArrayHasKey('memory_usage', $metrics);
        $this->assertArrayHasKey('status', $metrics);
        $this->assertEquals('success', $metrics['status']);
    }
}

class TestAction extends BaseAction
{
    protected function handle(): mixed
    {
        return ['processed' => $this->data['name']];
    }
}

class ValidationTestAction extends BaseAction
{
    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'name' => 'required|string',
        ];
    }
    protected function handle(): mixed
    {
        return ['validated' => true];
    }
}

class CacheableTestAction extends BaseAction
{
    protected bool $shouldCache = true;
    protected function handle(): mixed
    {
        return ['processed_at' => now()->toISOString()];
    }
}

class RetryableTestAction extends BaseAction
{
    protected static int $attempts = 0;
    protected function handle(): mixed
    {
        static::$attempts++;
        if (static::$attempts <= $this->data['fail_times']) {
            throw new \Exception('Simulated failure');
        }
        return ['success_on_attempt' => static::$attempts];
    }
}
