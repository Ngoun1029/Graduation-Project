<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get per_page from query parameter, restrict to 5, 15, 30
        $perPage = $request->query('per_page', 15);
        if (!in_array($perPage, [5, 15, 30])) {
            $perPage = 15;
        }
        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * $perPage;

        // // Start building the query with relationships
        $query = User::with('role');

        // Fetch users with pagination
        $users = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($perPage + 1) // Take one extra to check for next page
            ->get();

        // Check if there's a next page
        $hasNextPage = $users->count() > $perPage;
        if ($hasNextPage) {
            $users = $users->slice(0, $perPage);
        }

        // Format the users
        $formattedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'role' => $user->role ? $user->role->name : null,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'image' => $user->image,
                'phone' => $user->phone,
                'status' => $user->status,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Return response with metadata
        return response()->json([
            'status' => 'success',
            'data' => $formattedUsers,
            'metadata' => [
                'per_page' => (int) $perPage,
                'current_page' => $page,
                'has_next_page' => $hasNextPage,
                'from' => $offset + 1,
                'to' => $offset + $users->count(),
            ]
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $users = User::with('role')->findOrFail($id);

        $formattedUsers = [
            'id' => $users->id,
            'role' => $users->role ? $users->role->name : null,
            'full_name' => $users->full_name,
            'email' => $users->email,
            'image' => $users->image,
            'phone' => $users->phone,
            'status' => $users->status,
            'created_at' => $users->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $users->updated_at->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => $formattedUsers
        ], 200);
    }


    /**
     * Ban the specified user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function banUser(string $id)
    {
        $users = User::findOrFail($id);
        try {
            $users->update(['status' => 1]);
            return response()->json([
                'verified' => true,
                'status' => 'success',
                'message' => 'User banned successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => Str::limit($e->getMessage(), 150, '...'),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unbanUser(string $id)
    {
        $users = User::findOrFail($id);
        try {
            $users->update(['status' => 0]);
            return response()->json([
                'verified' => true,
                'status' => 'success',
                'message' => 'User unbanned successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => Str::limit($e->getMessage(), 150, '...'),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
