<?php

namespace App\Http\Controllers;

use App\Models\UserRecord;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Log;

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
                'name'  => $row ?? 'Unknown',
                'email' => $row ?? 'No Email'
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
}