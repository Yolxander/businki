<?php

namespace App\Console\Commands;

use App\Models\Chat;
use Illuminate\Console\Command;

class CleanupTemporaryChats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chats:cleanup-temporary {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up temporary chats with 3 or fewer messages older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Running in dry-run mode - no changes will be made');
        }

        try {
            $temporaryChats = Chat::whereHas('messages', function ($query) {
                $query->havingRaw('COUNT(*) <= 3');
            })
            ->where('created_at', '<', now()->subHours(24))
            ->with('messages')
            ->get();

            if ($temporaryChats->isEmpty()) {
                $this->info('No temporary chats found to clean up.');
                return 0;
            }

            $this->info("Found {$temporaryChats->count()} temporary chats to clean up:");

            foreach ($temporaryChats as $chat) {
                $messageCount = $chat->messages()->count();
                $age = $chat->created_at->diffForHumans();

                $this->line("- Chat ID: {$chat->id}, Messages: {$messageCount}, Age: {$age}");
            }

            if ($isDryRun) {
                $this->info('Dry run completed. No chats were deleted.');
                return 0;
            }

            if ($this->confirm('Do you want to proceed with deleting these temporary chats?')) {
                $deletedCount = Chat::cleanupTemporaryChats();
                $this->info("Successfully cleaned up {$deletedCount} temporary chats.");
            } else {
                $this->info('Cleanup cancelled.');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Error during cleanup: ' . $e->getMessage());
            return 1;
        }
    }
}
