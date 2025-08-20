<?php

namespace Litepie\Actions\Tests;

use Litepie\Actions\Manager\ActionManager;
use Litepie\Actions\BaseAction;
use Orchestra\Testbench\TestCase;

class ActionManagerTest extends TestCase
{
    protected ActionManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new ActionManager($this->app);
    }

    /** @test */
    public function it_can_register_and_execute_actions()
    {
        $this->manager->register('test-action', TestManagerAction::class);
        $result = $this->manager->execute('test-action', ['name' => 'test']);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(['processed' => 'test'], $result->getData());
    }

    /** @test */
    public function it_can_chain_actions()
    {
        $this->manager->register('action1', ActionOne::class);
        $this->manager->register('action2', ActionTwo::class);
        $results = $this->manager->chain([
            ['name' => 'action1', 'data' => ['value' => 1]],
            ['name' => 'action2', 'data' => ['value' => 2], 'use_previous_result' => true],
        ]);
        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn($result) => $result->isSuccess()));
    }

    /** @test */
    public function it_tracks_execution_history()
    {
        $this->manager->register('test-action', TestManagerAction::class);
        $this->manager->execute('test-action', ['name' => 'test1']);
        $this->manager->execute('test-action', ['name' => 'test2']);
        $history = $this->manager->getHistory();
        $this->assertCount(2, $history);
    }

    /** @test */
    public function it_provides_statistics()
    {
        $this->manager->register('test-action', TestManagerAction::class);
        $this->manager->execute('test-action', ['name' => 'test']);
        $stats = $this->manager->getStatistics();
        $this->assertEquals(1, $stats['total_executions']);
        $this->assertEquals(1, $stats['successful']);
        $this->assertEquals(0, $stats['failed']);
        $this->assertEquals(100, $stats['success_rate']);
    }
}

class TestManagerAction extends BaseAction
{
    protected function handle(): mixed
    {
        return ['processed' => $this->data['name']];
    }
}

class ActionOne extends BaseAction
{
    protected function handle(): mixed
    {
        return ['result1' => $this->data['value'] * 2];
    }
}

class ActionTwo extends BaseAction
{
    protected function handle(): mixed
    {
        $previousResult = $this->data['previous_result'] ?? [];
        return ['result2' => $this->data['value'] + ($previousResult['result1'] ?? 0)];
    }
}
