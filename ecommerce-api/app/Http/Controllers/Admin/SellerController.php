<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellerController extends Controller
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
        $query = User::with(['role', 'seller']);

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
                'shop_name' => $user->seller ? $user->seller->shop_name : null,
                'rating' => $user->seller ? $user->seller->rating : null,
                'balance' => $user->seller ? $user->seller->balance : null,
                'verification_status' => $user->seller ? $user->seller->verification_status : null,
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

    public function approval(string $id)
    {
        $seller = Seller::findOrFail($id);
        try {
            $seller->update(['status' => 1]);
            return response()->json(
                [
                    'verified' => true,
                    'status' => 'success',
                    'message' => 'Seller approved successfully.'
                ],
                200
            );
        } catch (Exception $e) {
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => Str::limit($e->getMessage(), 150, '...'),
            ], 500);
        }
    }

    public function rejection(string $id){
        $seller = Seller::findOrFail($id);
        try {
            $seller->update(['status' => 0]);
            return response()->json(
                [
                    'verified' => true,
                    'status' => 'success',
                    'message' => 'Seller rejected successfully.'
                ],
                200
            );
        } catch (Exception $e) {
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => Str::limit($e->getMessage(), 150, '...'),
            ], 500);
        }
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
    public function store(Request $request, $id)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $seller = Seller::with('user')->findOrFail($id);
        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => $seller
        ], 200);
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
