<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;

class UpdateOverdueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-overdue-tasks {--status= : The new status to set for overdue tasks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find overdue tasks and update their status to the one provided.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $newStatus = $this->option('status');

        if ($newStatus) {
            $validator = validator(['status' => $newStatus], [
                'status' => [new Enum(TaskStatus::class)]
            ]);

            if ($validator->fails()) {
                $this->error('Invalid status provided. Please use one of: pending, in_progress, completed, in_review, cancelled.');
                return;
            }
        }

        $this->info('Checking for overdue tasks...');

        $overdueTasks = Task::where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', [TaskStatus::COMPLETED, TaskStatus::CANCELLED])
            ->get();

        if ($overdueTasks->isEmpty()) {
            $this->info('No overdue tasks found.');
            return;
        }

        $updatedCount = 0;
        foreach ($overdueTasks as $task) {
            if ($newStatus) {
                $task->status = $newStatus;
                $task->save();
                $updatedCount++;
            }
            Log::info("Task #{$task->id} '{$task->name}' is overdue. New status: " . ($newStatus ?? 'Unchanged'));
        }

        if ($updatedCount > 0) {
            $this->info("Successfully updated {$updatedCount} overdue tasks to '{$newStatus}'.");
        } else {
            $this->info("Found {$overdueTasks->count()} overdue tasks. No status change was requested.");
        }
    }
}
