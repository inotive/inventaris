<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MonitorQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:monitor {--watch : Watch mode (refresh every 2 seconds)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor queue status and jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('watch')) {
            $this->watchMode();
        } else {
            $this->displayStatus();
        }

        return Command::SUCCESS;
    }

    /**
     * Watch mode - refresh every 2 seconds
     */
    private function watchMode()
    {
        $this->info('Queue Monitor - Watch Mode (Press Ctrl+C to exit)');
        $this->line('');

        while (true) {
            // Clear screen
            if (PHP_OS_FAMILY === 'Windows') {
                system('cls');
            } else {
                system('clear');
            }

            $this->displayStatus();
            sleep(2);
        }
    }

    /**
     * Display queue status
     */
    private function displayStatus()
    {
        $driver = config('queue.default');

        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║              QUEUE STATUS MONITOR                          ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->line('');

        $this->info("Queue Driver: " . strtoupper($driver));
        $this->info("Time: " . now()->format('Y-m-d H:i:s'));
        $this->line('');

        if ($driver === 'database') {
            $this->monitorDatabaseQueue();
        } elseif ($driver === 'redis') {
            $this->monitorRedisQueue();
        } elseif ($driver === 'sync') {
            $this->warn('⚠️  Queue driver is set to SYNC (no queue, jobs run immediately)');
        } else {
            $this->warn("Queue driver '{$driver}' monitoring not implemented");
        }

        $this->line('');
        $this->info('─────────────────────────────────────────────────────────────');
    }

    /**
     * Monitor database queue
     */
    private function monitorDatabaseQueue()
    {
        try {
            // Pending jobs
            $pending = DB::table('jobs')->count();
            $this->line("📋 Pending Jobs: <fg=yellow>{$pending}</>");

            // Failed jobs
            $failed = DB::table('failed_jobs')->count();
            if ($failed > 0) {
                $this->line("❌ Failed Jobs: <fg=red>{$failed}</>");
            } else {
                $this->line("✅ Failed Jobs: <fg=green>{$failed}</>");
            }

            $this->line('');

            // Jobs by queue
            $byQueue = DB::table('jobs')
                ->select('queue', DB::raw('COUNT(*) as total'))
                ->groupBy('queue')
                ->get();

            if ($byQueue->isNotEmpty()) {
                $this->info('Jobs by Queue:');
                foreach ($byQueue as $queue) {
                    $this->line("  • {$queue->queue}: <fg=cyan>{$queue->total}</>");
                }
                $this->line('');
            }

            // Recent jobs with human-readable available_at
            if ($pending > 0) {
                $this->info('Recent Pending Jobs (Last 5):');
                $recent = DB::table('jobs')
                    ->orderBy('id', 'desc')
                    ->limit(5)
                    ->get(['id', 'queue', 'attempts', 'available_at', 'created_at']);

                $now = time();
                $headers = ['ID', 'Queue', 'Attempts', 'Available At', 'Status'];
                $rows = $recent->map(function ($job) use ($now) {
                    $availableAt = $job->available_at;
                    $availableDate = date('Y-m-d H:i:s', $availableAt);

                    // Calculate time difference
                    $diff = $availableAt - $now;

                    if ($diff <= 0) {
                        $status = '<fg=green>Ready now</>';
                    } elseif ($diff < 60) {
                        $status = "<fg=yellow>In {$diff}s</>";
                    } elseif ($diff < 3600) {
                        $minutes = round($diff / 60);
                        $status = "<fg=yellow>In {$minutes}m</>";
                    } elseif ($diff < 86400) {
                        $hours = round($diff / 3600, 1);
                        $status = "<fg=cyan>In {$hours}h</>";
                    } else {
                        $days = round($diff / 86400, 1);
                        $status = "<fg=magenta>In {$days}d</>";
                    }

                    return [
                        $job->id,
                        $job->queue,
                        $job->attempts,
                        $availableDate,
                        $status,
                    ];
                });

                $this->table($headers, $rows);
            }

            // Recent failed jobs
            if ($failed > 0) {
                $this->line('');
                $this->warn('Recent Failed Jobs (Last 3):');
                $recentFailed = DB::table('failed_jobs')
                    ->orderBy('failed_at', 'desc')
                    ->limit(3)
                    ->get(['id', 'queue', 'failed_at']);

                $headers = ['ID', 'Queue', 'Failed At'];
                $rows = $recentFailed->map(function ($job) {
                    return [
                        $job->id,
                        $job->queue ?? 'default',
                        $job->failed_at,
                    ];
                });

                $this->table($headers, $rows);
            }

            // Check if queue worker is running
            $this->line('');
            $this->checkWorkerStatus();

        } catch (\Exception $e) {
            $this->error('Error monitoring database queue: ' . $e->getMessage());
        }
    }

    /**
     * Monitor Redis queue
     */
    private function monitorRedisQueue()
    {
        try {
            // Check Redis connection
            Redis::ping();

            $queues = ['default', 'notifications', 'high', 'low'];

            $this->info('Queue Lengths:');
            foreach ($queues as $queue) {
                $length = Redis::llen("queues:{$queue}");
                if ($length > 0) {
                    $this->line("  • {$queue}: <fg=cyan>{$length}</>");
                }
            }

            $this->line('');
            $this->checkWorkerStatus();

        } catch (\Exception $e) {
            $this->error('Error monitoring Redis queue: ' . $e->getMessage());
            $this->line('Make sure Redis is running and configured correctly.');
        }
    }

    /**
     * Check if queue worker is running
     */
    private function checkWorkerStatus()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows
            exec('tasklist /FI "IMAGENAME eq php.exe" 2>NUL | find /I "php.exe"', $output);
        } else {
            // Linux/Mac
            exec('ps aux | grep "queue:work" | grep -v grep', $output);
        }

        if (!empty($output)) {
            $this->info('✅ Queue Worker: <fg=green>RUNNING</>');
            $this->line('   Workers: ' . count($output));
        } else {
            $this->warn('⚠️  Queue Worker: <fg=red>NOT RUNNING</>');
            $this->line('   Start with: <fg=cyan>php artisan queue:work</>');
        }
    }
}
