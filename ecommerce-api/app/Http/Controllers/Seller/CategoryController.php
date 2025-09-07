<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        if (!in_array($perPage, [5, 15, 30])) {
            $perPage = 15;
        }
        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * $perPage;

        // // Start building the query with relationships
        $query = Category::query();

        // Fetch users with pagination
        $categories = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($perPage + 1) // Take one extra to check for next page
            ->get();

        // Check if there's a next page
        $hasNextPage = $categories->count() > $perPage;
        if ($hasNextPage) {
            $categories = $categories->slice(0, $perPage);
        }

        // Format the users
        $formattedCategories = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'created_at' => $category->created_at
            ];
        });

        // Return response with metadata
        return response()->json([
            'status' => 'success',
            'data' => $formattedCategories,
            'metadata' => [
                'per_page' => (int) $perPage,
                'current_page' => $page,
                'has_next_page' => $hasNextPage,
                'from' => $offset + 1,
                'to' => $offset + $categories->count(),
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
        $request->validate([
            'name' => 'required|string',
        ]);

        if (Category::where('name', $request->name)->first()) {
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => 'name have been created',
            ]);
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'verified' => true,
            'status' => 'success',
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        $categories = Category::findOrFail($id);
        $request->validate([
            'name' => 'required|string',
        ]);

        $categories->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'verified' => true,
            'status' => 'success',
            'message' => 'Category updated successfully',
            'data' => $categories,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $categories = Category::findOrFail($id);
        $categories->delete();
        return response()->json([
            'verified' => true,
            'status' => 'success',
            'message' => 'Category deleted successfully',
        ], 200);
    }
}
