<?php

namespace Tests\Unit\Tasks;

use App\Models\Tasks\RecurringTask;
use App\Models\Users\User;
use Carbon\Carbon;
use Tests\TestCase;

class SetRecurringTaskDate extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function testInvalidFrequency()
    {
        $task = RecurringTask::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
            'frequency_unit' => 'badUnit',
            'frequency_amount' => 3
        ]);
        $this->assertThrows(function () use ($task) {
            $task->createFutureTask([]);
        });
    }

    public function testDaysFrequency()
    {
        $task = RecurringTask::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
            'frequency_unit' => 'day',
            'frequency_amount' => 3
        ]);
        $futureTask = $task->createFutureTask([]);
        $expectedDueDate = Carbon::now()->addDays(3);

        self::assertTrue($expectedDueDate->isSameDay($futureTask->due_date));
    }

    public function testWeeksFrequency()
    {
        $task = RecurringTask::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
            'frequency_unit' => 'week',
            'frequency_amount' => 3
        ]);
        $futureTask = $task->createFutureTask([]);
        $expectedDueDate = Carbon::now()->addWeeks(3);

        self::assertTrue($expectedDueDate->isSameDay($futureTask->due_date));
    }

    public function testMonthsFrequency()
    {
        $task = RecurringTask::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
            'frequency_unit' => 'month',
            'frequency_amount' => 3
        ]);
        $futureTask = $task->createFutureTask([]);
        $expectedDueDate = Carbon::now()->addMonths(3);

        self::assertTrue($expectedDueDate->isSameDay($futureTask->due_date));
    }

    public function testYearsFrequency()
    {
        $task = RecurringTask::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
            'frequency_unit' => 'year',
            'frequency_amount' => 3
        ]);
        $futureTask = $task->createFutureTask([]);
        $expectedDueDate = Carbon::now()->addYears(3);

        self::assertTrue($expectedDueDate->isSameDay($futureTask->due_date));
    }

    public function testDuplicateFutureTask()
    {
        $task = RecurringTask::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
            'frequency_unit' => 'year',
            'frequency_amount' => 3
        ]);
        $futureTask1 = $task->createFutureTask([]);
        $futureTask2 = $task->createFutureTask([]);

        self::assertEquals($futureTask1->id, $futureTask2->id);
    }
}
