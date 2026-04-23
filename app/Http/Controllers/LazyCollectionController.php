<?php

namespace App\Http\Controllers;

use App\Models\UserRecord;

class LazyCollectionController extends Controller
{
    // ==============================
    // 1. Lazy Collection (cursor)
    // ==============================
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

    // ==============================
    // 2. Chunk Processing
    // ==============================
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

    // ==============================
    // 3. Memory Comparison
    // ==============================
    public function memoryComparison()
    {
        // Normal Collection (Eager Load)
        $start1 = memory_get_usage();
        $users1 = UserRecord::all();

        foreach ($users1 as $user) {
            // processing
        }
        $end1 = memory_get_usage();

        // Lazy Collection (cursor)
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

    // ==============================
    // 4. Lazy Search
    // ==============================
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

                flush(); // send output immediately
            }

            echo "]";
        }, 200, [
            "Content-Type" => "application/json",
            "Cache-Control" => "no-cache",
        ]);
    }
}
