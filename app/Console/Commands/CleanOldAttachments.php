<?php

namespace App\Console\Commands;

use App\Models\Attachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanOldAttachments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-old-attachments {--days=30 : Delete attachments older than this number of days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old and orphaned file attachments from storage and database.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting the cleanup of old and orphaned attachments...');

        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $oldAttachments = Attachment::where('created_at', '<', $cutoffDate)->get();

        $orphanedAttachments = Attachment::whereDoesntHave('attachable')->get();

        $attachmentsToDelete = $oldAttachments->merge($orphanedAttachments)->unique('id');

        if ($attachmentsToDelete->isEmpty()) {
            $this->info('No old or orphaned attachments found to delete.');
            return;
        }

        $this->warn("Found {$attachmentsToDelete->count()} attachments to delete. This action cannot be undone.");

        if (!$this->confirm('Do you wish to continue with the deletion?')) {
            $this->info('Cleanup cancelled by user.');
            return;
        }

        $progressBar = $this->output->createProgressBar($attachmentsToDelete->count());
        $progressBar->start();

        $deletedCount = 0;
        foreach ($attachmentsToDelete as $attachment) {
            Storage::disk($attachment->disk)->delete($attachment->path);

            $attachment->delete();
            $deletedCount++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Successfully deleted {$deletedCount} attachments.");
    }
}
