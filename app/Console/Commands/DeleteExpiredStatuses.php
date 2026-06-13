<?php

namespace App\Console\Commands;

use App\Models\Status;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteExpiredStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes WhatsApp statuses that are older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredStatuses = Status::where('expires_at', '<=', now())->get();
        $count = $expiredStatuses->count();

        foreach ($expiredStatuses as $status) {
            // Delete media from storage if exists
            if ($status->media_path) {
                Storage::disk('public')->delete($status->media_path);
            }
            $status->delete();
        }

        $this->info("Successfully deleted {$count} expired status stories.");
        return Command::SUCCESS;
    }
}
