<?php

namespace Litepie\Actions\Tests;

use Litepie\Actions\Batch\ActionBatch;
use Orchestra\Testbench\TestCase;

class BatchTest extends TestCase
{
    /** @test */
    public function it_can_execute_batch_of_actions()
    {
        $batch = ActionBatch::make()
            ->add(new TestAction(['name' => 'test1']))
            ->add(new TestAction(['name' => 'test2']));
        $results = $batch->execute();
        $this->assertCount(2, $results);
        $this->assertTrue($batch->isAllSuccessful());
    }

    /** @test */
    public function it_can_stop_on_failure()
    {
        $batch = ActionBatch::make()
            ->stopOnFailure()
            ->add(new TestAction(['name' => 'test1']))
            ->add(new FailingTestAction(['name' => 'test2']))
            ->add(new TestAction(['name' => 'test3']));
        $results = $batch->execute();
        $this->assertCount(2, $results); // Should stop after failure
        $this->assertFalse($batch->isAllSuccessful());
    }
}

class FailingTestAction extends \Litepie\Actions\BaseAction
{
    protected function handle(): mixed
    {
        throw new \Exception('This action always fails');
    }
}
