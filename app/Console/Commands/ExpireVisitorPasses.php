<?php

namespace App\Console\Commands;

use App\Services\VisitorService;
use Illuminate\Console\Command;

class ExpireVisitorPasses extends Command
{
    protected $signature = 'visitors:expire';

    protected $description = 'Expire active visitor passes whose visit date has passed';

    public function handle(VisitorService $visitorService): int
    {
        $visitorService->expireVisitors();

        $this->info('Expired visitor passes processed.');

        return self::SUCCESS;
    }
}
