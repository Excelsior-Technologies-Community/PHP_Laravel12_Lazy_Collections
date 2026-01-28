<?php

namespace App\Http\Controllers;

use App\Models\UserRecord;

class LazyCollectionController extends Controller
{
    public function index()
    {
        // cursor() = Lazy Collection
        $users = UserRecord::cursor();

        $response = [];

        foreach ($users as $user) {
            // Processing ONE record at a time
            $response[] = [
                'id'    => $user->id,
                'name'  => strtoupper($user->name),
                'email' => $user->email,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Lazy Collection Example',
            'data' => $response
        ]);
    }
}
