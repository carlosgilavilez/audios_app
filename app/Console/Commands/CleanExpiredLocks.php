<?php

namespace App\Console\Commands;

use App\Models\EditingLock;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanExpiredLocks extends Command
{
    protected $signature = 'locks:clean-expired';
    protected $description = 'Remove expired editing locks';

    public function handle(): int
    {
        $count = EditingLock::where('expires_at', '<', Carbon::now())->delete();
        $this->info("Removed {$count} expired locks");
        return self::SUCCESS;
    }
}

