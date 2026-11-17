<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class RetryDeadEmails extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'queue:retry-dead';

    /**
     * The console command description.
     */
    protected $description = 'Requeue failed messages from the dead-letter queue back to the active queue';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connection = Queue::connection('rabbitmq');
        $from = 'orders.dead';
        $to = 'emails';
        $count = 0;

        // Pop messages from the dead-letter queue and push them back to the active queue
        while ($job = $connection->pop($from)) {
            $connection->pushRaw($job->getRawBody(), $to);
            $job->delete();
            $count++;
        }

        // Check if any messages were processed
        if ($count > 0) {
            $this->info("Successfully requeued ({$count}) message(s) from [{$from}] to [{$to}].");
        } else {
            $this->warn("The dead-letter queue [{$from}] is currently empty.");
        }

        return self::SUCCESS;
    }
}