<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateLargeCsv extends Command
{
    protected $signature = 'csv:generate {rows=10000}';
    protected $description = 'Generate a large CSV file for testing';

    public function handle()
    {
        $rows = $this->argument('rows');
        $filePath = storage_path('app/large_data.csv');
        
        $handle = fopen($filePath, 'w');
        
        // Add headers
        fputcsv($handle, ['name', 'email', 'created_at']);
        
        $bar = $this->output->createProgressBar($rows);
        
        for ($i = 1; $i <= $rows; $i++) {
            fputcsv($handle, [
                "User {$i}",
                "user{$i}@example.com",
                now()->subDays(rand(1, 365))->toDateTimeString(),
            ]);
            $bar->advance();
        }
        
        fclose($handle);
        $bar->finish();
        
        $this->newLine();
        $this->info("Generated {$rows} rows in {$filePath}");
        
        // Show file size
        $size = filesize($filePath);
        $this->info("File size: " . round($size / 1024 / 1024, 2) . " MB");
    }
}