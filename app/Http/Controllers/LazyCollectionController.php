<?php

namespace App\Http\Controllers;

use App\Models\UserRecord;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LazyCollectionController extends Controller
{
    public function index()
    {
        $users = UserRecord::cursor();

        $response = [];

        foreach ($users as $user) {
            $response[] = [
                'id'    => $user->id,
                'name'  => strtoupper($user->name),
                'email' => $user->email,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Lazy Collection (cursor) Example',
            'data' => $response
        ]);
    }

    public function chunkUsers()
    {
        $response = [];

        UserRecord::chunk(2, function ($users) use (&$response) {
            foreach ($users as $user) {
                $response[] = [
                    'id'    => $user->id,
                    'name'  => strtoupper($user->name),
                    'email' => $user->email,
                ];
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Chunk Processing Example',
            'data' => $response
        ]);
    }

    public function memoryComparison()
    {
        $start1 = memory_get_usage();
        $users1 = UserRecord::all();

        foreach ($users1 as $user) {
            // processing
        }
        $end1 = memory_get_usage();

        $start2 = memory_get_usage();
        $users2 = UserRecord::cursor();

        foreach ($users2 as $user) {
            // processing
        }
        $end2 = memory_get_usage();

        return response()->json([
            'status' => true,
            'message' => 'Memory Usage Comparison',
            'normal_collection_memory' => $end1 - $start1,
            'lazy_collection_memory'   => $end2 - $start2,
        ]);
    }

    public function search($keyword)
    {
        $users = UserRecord::cursor()->filter(function ($user) use ($keyword) {
            return str_contains(strtolower($user->name), strtolower($keyword));
        });

        return response()->json([
            'status' => true,
            'message' => 'Lazy Search Result',
            'data' => $users->values()
        ]);
    }

    public function streamUsers()
    {
        return response()->stream(function () {
            echo "[";
            $first = true;

            foreach (UserRecord::cursor() as $user) {
                if (!$first) {
                    echo ",";
                }
                $first = false;

                echo json_encode([
                    'id' => $user->id,
                    'name' => strtoupper($user->name),
                    'email' => $user->email,
                ]);

                flush();
            }
            echo "]";
        }, 200, [
            "Content-Type" => "application/json",
            "Cache-Control" => "no-cache",
        ]);
    }

    public function readLargeFile()
    {
        $filePath = storage_path('app/large_data.csv');

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found in storage/app/large_data.csv']);
        }

        $records = LazyCollection::make(function () use ($filePath) {
            $handle = fopen($filePath, 'r');
            while (($line = fgetcsv($handle)) !== false) {
                yield $line;
            }
            fclose($handle);
        })
        ->skip(1)
        ->map(function($row) {
            return [
                'name'  => $row[0] ?? 'Unknown',
                'email' => $row[1] ?? 'No Email'
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Lazy File Reader Example',
            'data' => $records->take(10)->values()
        ]);
    }

    public function tapProgress()
    {
        $processedCount = 0;

        $users = UserRecord::cursor()
            ->tap(function ($user) use (&$processedCount) {
                $processedCount++;
                Log::info("Lazy Progress Tracking: Processing ID {$user->id}");
            })
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'log_status' => 'Logged in laravel.log'
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Tap Progress Example (Check Logs)',
            'processed_count_hint' => $processedCount,
            'data' => $users->take(5)->values()
        ]);
    }

    public function combinedSources()
    {
        $dbSource = UserRecord::cursor();

        $externalSource = LazyCollection::make(function () {
            yield (object) ['id' => 101, 'name' => 'External User 1', 'email' => 'ext1@example.com'];
            yield (object) ['id' => 102, 'name' => 'External User 2', 'email' => 'ext2@example.com'];
        });

        $combined = $dbSource->concat($externalSource);

        return response()->json([
            'status' => true,
            'message' => 'Multi-Source Lazy Combination',
            'data' => $combined->values()
        ]);
    }

    // ============ NEW FUNCTIONALITY METHODS ============

    /**
     * NEW: Batch Processing with Progress Tracking
     * Process records in batches with real-time progress
     * URL: /batch-process
     */
    public function batchProcess()
    {
        $batchSize = 2;
        $totalRecords = UserRecord::count();
        $processedCount = 0;
        $batchesProcessed = 0;
        $results = [];

        $startTime = microtime(true);

        UserRecord::chunk($batchSize, function ($users) use (&$processedCount, &$batchesProcessed, &$results, $batchSize) {
            $batchesProcessed++;
            
            foreach ($users as $user) {
                // Simulate some processing
                $processedCount++;
                $results[] = [
                    'batch_id' => $batchesProcessed,
                    'user_id' => $user->id,
                    'user_name' => strtoupper($user->name),
                    'processed_at' => now()->toDateTimeString(),
                ];
            }

            // Log batch completion
            Log::info("Batch {$batchesProcessed} completed. Processed {$processedCount} records so far.");
        });

        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);

        return response()->json([
            'status' => true,
            'message' => 'Batch Processing Completed',
            'summary' => [
                'total_records' => $totalRecords,
                'records_processed' => $processedCount,
                'total_batches' => $batchesProcessed,
                'batch_size' => $batchSize,
                'execution_time_seconds' => $executionTime,
            ],
            'sample_results' => array_slice($results, 0, 10)
        ]);
    }

    /**
     * NEW: Export to CSV using Lazy Collection
     * Stream CSV export without loading all data into memory
     * URL: /export-csv
     */
    public function exportToCsv()
    {
        $fileName = 'users_export_' . date('Y-m-d_His') . '.csv';

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($handle, ['ID', 'Name', 'Email', 'Created At']);
            
            // Stream data using cursor (lazy loading)
            foreach (UserRecord::cursor() as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->created_at
                ]);
                
                // Flush output buffer periodically
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * NEW: Advanced Filter with Multiple Conditions
     * Filter users using multiple criteria with lazy collection
     * URL: /advanced-filter/{keyword?}
     */
    public function advancedFilter(Request $request, $keyword = null)
    {
        $domain = $request->get('domain', 'gmail.com');
        
        $filteredUsers = UserRecord::cursor()
            ->filter(function ($user) use ($keyword, $domain) {
                $matchesKeyword = true;
                $matchesDomain = true;
                
                if ($keyword) {
                    $matchesKeyword = stripos($user->name, $keyword) !== false;
                }
                
                if ($domain) {
                    $matchesDomain = str_contains($user->email, $domain);
                }
                
                return $matchesKeyword && $matchesDomain;
            })
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_domain' => explode('@', $user->email)[1] ?? 'unknown',
                    'name_length' => strlen($user->name),
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Advanced Filter Results',
            'filters_applied' => [
                'keyword' => $keyword ?? 'none',
                'domain' => $domain,
            ],
            'total_matches' => $filteredUsers->count(),
            'data' => $filteredUsers->values()
        ]);
    }

    /**
     * NEW: Aggregate Statistics using Map & Reduce
     * Perform calculations without loading all data
     * URL: /aggregate-stats
     */
    public function aggregateStats()
    {
        $stats = UserRecord::cursor()
            ->map(function ($user) {
                return [
                    'name_length' => strlen($user->name),
                    'email_length' => strlen($user->email),
                    'has_gmail' => str_contains($user->email, 'gmail.com'),
                    'has_yahoo' => str_contains($user->email, 'yahoo.com'),
                    'created_month' => $user->created_at->format('F'),
                ];
            })
            ->reduce(function ($carry, $item) {
                $carry['total_name_length'] += $item['name_length'];
                $carry['total_email_length'] += $item['email_length'];
                $carry['gmail_count'] += $item['has_gmail'] ? 1 : 0;
                $carry['yahoo_count'] += $item['has_yahoo'] ? 1 : 0;
                $carry['month_counts'][$item['created_month']] = 
                    ($carry['month_counts'][$item['created_month']] ?? 0) + 1;
                $carry['record_count']++;
                
                return $carry;
            }, [
                'total_name_length' => 0,
                'total_email_length' => 0,
                'gmail_count' => 0,
                'yahoo_count' => 0,
                'month_counts' => [],
                'record_count' => 0,
            ]);

        $totalRecords = $stats['record_count'];
        
        return response()->json([
            'status' => true,
            'message' => 'Aggregate Statistics (Using Map + Reduce)',
            'statistics' => [
                'total_records_analyzed' => $totalRecords,
                'average_name_length' => $totalRecords > 0 ? round($stats['total_name_length'] / $totalRecords, 2) : 0,
                'average_email_length' => $totalRecords > 0 ? round($stats['total_email_length'] / $totalRecords, 2) : 0,
                'gmail_users' => $stats['gmail_count'],
                'yahoo_users' => $stats['yahoo_count'],
                'users_by_month' => $stats['month_counts'],
            ]
        ]);
    }

    /**
     * NEW: Pagination with Lazy Collection
     * Implement custom pagination using lazy collections
     * URL: /lazy-paginate/{page?}
     */
    public function lazyPaginate($page = 1)
    {
        $perPage = 3;
        $skip = ($page - 1) * $perPage;
        
        // Get total count efficiently
        $total = UserRecord::count();
        
        // Use lazy collection with skip and take
        $users = UserRecord::cursor()
            ->skip($skip)
            ->take($perPage)
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            });
        
        $totalPages = ceil($total / $perPage);
        
        return response()->json([
            'status' => true,
            'message' => 'Lazy Pagination Example',
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_previous' => $page > 1,
                'next_page_url' => $page < $totalPages ? url("/lazy-paginate/" . ($page + 1)) : null,
                'prev_page_url' => $page > 1 ? url("/lazy-paginate/" . ($page - 1)) : null,
            ],
            'data' => $users->values()
        ]);
    }

    /**
     * NEW: Data Transformation Pipeline
     * Chain multiple transformations using lazy collections
     * URL: /transform-pipeline
     */
    public function transformPipeline()
    {
        $transformedData = UserRecord::cursor()
            // Step 1: Filter active users (all are active in this example)
            ->filter(function ($user) {
                return $user->id > 0;
            })
            // Step 2: Transform user data
            ->map(function ($user) {
                return [
                    'original_id' => $user->id,
                    'original_name' => $user->name,
                    'original_email' => $user->email,
                ];
            })
            // Step 3: Enrich with additional data
            ->map(function ($user) {
                $user['email_username'] = explode('@', $user['original_email'])[0];
                $user['email_domain'] = explode('@', $user['original_email'])[1] ?? 'unknown';
                $user['name_initials'] = implode('', array_map(function($word) {
                    return strtoupper($word[0]);
                }, explode(' ', $user['original_name'])));
                return $user;
            })
            // Step 4: Format for display
            ->map(function ($user) {
                return [
                    'id' => $user['original_id'],
                    'display_name' => $user['original_name'] . ' (' . $user['name_initials'] . ')',
                    'email_info' => $user['email_username'] . '@' . $user['email_domain'],
                    'is_valid' => filter_var($user['original_email'], FILTER_VALIDATE_EMAIL) !== false,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Data Transformation Pipeline (4 Steps)',
            'pipeline_steps' => ['Filter', 'Transform', 'Enrich', 'Format'],
            'total_processed' => $transformedData->count(),
            'data' => $transformedData->values()
        ]);
    }

    /**
     * NEW: Email Notification Simulation
     * Simulate sending emails using lazy collection with rate limiting
     * URL: /send-notifications
     */
    public function sendNotifications()
    {
        $sentEmails = [];
        $failedEmails = [];
        $rateLimitDelay = 500000; // microseconds (0.5 seconds)
        
        $startTime = microtime(true);
        
        foreach (UserRecord::cursor() as $index => $user) {
            // Simulate email sending
            $status = $this->simulateEmailSend($user->email, $user->name);
            
            if ($status['success']) {
                $sentEmails[] = [
                    'to' => $user->email,
                    'name' => $user->name,
                    'subject' => 'Welcome to Our Platform',
                    'sent_at' => now()->toDateTimeString(),
                ];
            } else {
                $failedEmails[] = [
                    'to' => $user->email,
                    'name' => $user->name,
                    'error' => $status['error'],
                ];
            }
            
            // Simulate rate limiting (delay between emails)
            if ($rateLimitDelay > 0 && $index < UserRecord::count() - 1) {
                usleep($rateLimitDelay);
            }
        }
        
        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);
        
        return response()->json([
            'status' => true,
            'message' => 'Email Notification Simulation',
            'summary' => [
                'total_attempts' => count($sentEmails) + count($failedEmails),
                'successful' => count($sentEmails),
                'failed' => count($failedEmails),
                'execution_time_seconds' => $executionTime,
                'rate_limit_delay_ms' => $rateLimitDelay / 1000,
            ],
            'sent_emails_sample' => array_slice($sentEmails, 0, 5),
            'failed_emails' => $failedEmails,
        ]);
    }
    
    private function simulateEmailSend($email, $name)
    {
        // Simulate random success (90% success rate)
        $success = rand(1, 100) <= 90;
        
        if ($success) {
            Log::info("Email sent to: {$email} (Name: {$name})");
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'Simulated delivery failure'];
        }
    }

    /**
     * NEW: JSONL (JSON Lines) File Processing
     * Process large JSONL files line by line
     * URL: /process-jsonl
     */
    public function processJsonlFile()
    {
        $jsonlPath = storage_path('app/sample_data.jsonl');
        
        // Create sample JSONL file if it doesn't exist
        if (!file_exists($jsonlPath)) {
            $this->createSampleJsonlFile($jsonlPath);
        }
        
        $processedRecords = [];
        $totalLines = 0;
        $validRecords = 0;
        $invalidRecords = 0;
        
        $startTime = microtime(true);
        
        // Process JSONL file line by line using LazyCollection
        $records = LazyCollection::make(function () use ($jsonlPath) {
            $handle = fopen($jsonlPath, 'r');
            while (($line = fgets($handle)) !== false) {
                yield trim($line);
            }
            fclose($handle);
        })
        ->filter(function ($line) {
            return !empty($line);
        })
        ->map(function ($line) use (&$totalLines, &$validRecords, &$invalidRecords) {
            $totalLines++;
            $data = json_decode($line, true);
            
            if ($data && isset($data['name'], $data['email'])) {
                $validRecords++;
                return [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'processed_at' => now()->toDateTimeString(),
                ];
            } else {
                $invalidRecords++;
                return null;
            }
        })
        ->filter(); // Remove null values
        
        $endTime = microtime(true);
        
        return response()->json([
            'status' => true,
            'message' => 'JSONL File Processing (Line by Line)',
            'file_info' => [
                'file_path' => $jsonlPath,
                'file_size_bytes' => file_exists($jsonlPath) ? filesize($jsonlPath) : 0,
            ],
            'processing_stats' => [
                'total_lines_read' => $totalLines,
                'valid_records' => $validRecords,
                'invalid_records' => $invalidRecords,
                'execution_time_seconds' => round($endTime - $startTime, 2),
            ],
            'sample_records' => $records->take(5)->values()
        ]);
    }
    
    private function createSampleJsonlFile($path)
    {
        $handle = fopen($path, 'w');
        
        $sampleData = [
            ['name' => 'JSON User One', 'email' => 'json1@example.com', 'age' => 25],
            ['name' => 'JSON User Two', 'email' => 'json2@example.com', 'age' => 30],
            ['name' => 'JSON User Three', 'email' => 'json3@example.com', 'age' => 35],
            ['name' => 'JSON User Four', 'email' => 'json4@example.com', 'age' => 28],
            ['name' => 'JSON User Five', 'email' => 'json5@example.com', 'age' => 32],
            ['invalid_line_without_json_format'],
            ['name' => 'JSON User Six', 'email' => 'json6@example.com', 'age' => 29],
            ['name' => 'JSON User Seven', 'email' => 'json7@example.com', 'age' => 31],
            ['name' => 'JSON User Eight', 'email' => 'json8@example.com', 'age' => 27],
            ['name' => 'JSON User Nine', 'email' => 'json9@example.com', 'age' => 33],
            ['name' => 'JSON User Ten', 'email' => 'json10@example.com', 'age' => 26],
        ];
        
        foreach ($sampleData as $data) {
            fwrite($handle, json_encode($data) . "\n");
        }
        
        fclose($handle);
    }

    /**
     * NEW: Real-time Dashboard Stats
     * Generate dashboard statistics using lazy collections
     * URL: /dashboard-stats
     */
    public function dashboardStats()
    {
        $stats = UserRecord::cursor()
            ->reduce(function ($carry, $user) {
                // Count by email domain
                $domain = explode('@', $user->email)[1] ?? 'unknown';
                $carry['domain_counts'][$domain] = ($carry['domain_counts'][$domain] ?? 0) + 1;
                
                // Count by name length category
                $nameLength = strlen($user->name);
                if ($nameLength <= 5) {
                    $carry['name_length_categories']['short']++;
                } elseif ($nameLength <= 10) {
                    $carry['name_length_categories']['medium']++;
                } else {
                    $carry['name_length_categories']['long']++;
                }
                
                // Track first letter of name
                $firstLetter = strtoupper($user->name[0]);
                $carry['name_first_letters'][$firstLetter] = ($carry['name_first_letters'][$firstLetter] ?? 0) + 1;
                
                $carry['total_count']++;
                
                return $carry;
            }, [
                'domain_counts' => [],
                'name_length_categories' => ['short' => 0, 'medium' => 0, 'long' => 0],
                'name_first_letters' => [],
                'total_count' => 0,
            ]);
        
        // Calculate percentages
        $total = $stats['total_count'];
        $domainPercentages = [];
        foreach ($stats['domain_counts'] as $domain => $count) {
            $domainPercentages[$domain] = round(($count / $total) * 100, 2);
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Real-time Dashboard Statistics',
            'timestamp' => now()->toDateTimeString(),
            'dashboard_stats' => [
                'total_users' => $total,
                'email_domain_distribution' => $stats['domain_counts'],
                'email_domain_percentages' => $domainPercentages,
                'name_length_distribution' => $stats['name_length_categories'],
                'popular_name_start_letters' => collect($stats['name_first_letters'])
                    ->sortDesc()
                    ->take(5)
                    ->toArray(),
            ]
        ]);
    }

    /**
     * NEW: Batch Update with Lazy Collection
     * Update records in batches without memory issues
     * URL: /batch-update
     */
    public function batchUpdate()
    {
        $batchSize = 2;
        $updatedCount = 0;
        $startTime = microtime(true);
        
        // Get all user IDs using lazy collection
        $userIds = UserRecord::cursor()->pluck('id')->toArray();
        
        // Process in chunks
        $chunks = array_chunk($userIds, $batchSize);
        
        foreach ($chunks as $chunkIndex => $chunkIds) {
            // Simulate update operation
            $updated = UserRecord::whereIn('id', $chunkIds)->update([
                'updated_at' => now(),
            ]);
            
            $updatedCount += $updated;
            
            Log::info("Batch " . ($chunkIndex + 1) . ": Updated " . $updated . " records");
            
            // Small delay between batches to simulate real processing
            usleep(100000); // 0.1 second delay
        }
        
        $endTime = microtime(true);
        
        return response()->json([
            'status' => true,
            'message' => 'Batch Update Completed',
            'summary' => [
                'records_updated' => $updatedCount,
                'batch_size' => $batchSize,
                'total_batches' => count($chunks),
                'execution_time_seconds' => round($endTime - $startTime, 2),
            ],
            'verification_url' => url('/lazy-users')
        ]);
    }

    /**
     * NEW: Performance Test - Lazy vs Eager Loading
     * Detailed performance comparison
     * URL: /performance-test
     */
    public function performanceTest()
    {
        $results = [];
        
        // Test 1: Memory usage with 10,000 records
        $testRecords = 10000;
        $this->createTestRecords($testRecords);
        
        // Test Eager Loading (all at once)
        $startMemory = memory_get_usage();
        $startTime = microtime(true);
        
        $eagerUsers = UserRecord::take($testRecords)->get();
        foreach ($eagerUsers as $user) {
            $processed = $user->name; // Simulate processing
        }
        
        $eagerTime = microtime(true) - $startTime;
        $eagerMemory = memory_get_usage() - $startMemory;
        
        // Test Lazy Loading (cursor)
        $startMemory = memory_get_usage();
        $startTime = microtime(true);
        
        $lazyUsers = UserRecord::take($testRecords)->cursor();
        foreach ($lazyUsers as $user) {
            $processed = $user->name; // Simulate processing
        }
        
        $lazyTime = microtime(true) - $startTime;
        $lazyMemory = memory_get_usage() - $startMemory;
        
        // Clean up test records (optional - remove this if you want to keep them)
        // UserRecord::where('id', '>', 4)->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'Performance Test: Lazy vs Eager Loading',
            'test_parameters' => [
                'number_of_records' => $testRecords,
                'test_timestamp' => now()->toDateTimeString(),
            ],
            'results' => [
                'eager_loading' => [
                    'execution_time_seconds' => round($eagerTime, 4),
                    'memory_usage_bytes' => $eagerMemory,
                    'memory_usage_mb' => round($eagerMemory / 1024 / 1024, 4),
                    'peak_memory_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
                ],
                'lazy_loading' => [
                    'execution_time_seconds' => round($lazyTime, 4),
                    'memory_usage_bytes' => $lazyMemory,
                    'memory_usage_mb' => round($lazyMemory / 1024 / 1024, 4),
                    'peak_memory_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
                ],
                'comparison' => [
                    'memory_saved_percentage' => $eagerMemory > 0 ? round((($eagerMemory - $lazyMemory) / $eagerMemory) * 100, 2) : 0,
                    'time_difference_seconds' => round(abs($eagerTime - $lazyTime), 4),
                    'recommendation' => $lazyMemory < $eagerMemory ? 'Lazy Collection is more memory efficient' : 'Consider both approaches',
                ]
            ]
        ]);
    }
    
    private function createTestRecords($count)
    {
        $existingCount = UserRecord::count();
        
        if ($existingCount < $count) {
            $recordsToCreate = $count - $existingCount;
            $records = [];
            
            for ($i = $existingCount + 1; $i <= $count; $i++) {
                $records[] = [
                    'name' => "Test User {$i}",
                    'email' => "testuser{$i}@example.com",
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Insert in chunks to avoid memory issues
            foreach (array_chunk($records, 1000) as $chunk) {
                UserRecord::insert($chunk);
            }
        }
    }
}