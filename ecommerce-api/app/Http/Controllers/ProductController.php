<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Service\FileService;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

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
        $query = Product::query();

        if ($request->filled('name')) {
            $query->where('name', $request->name);
        }

        if ($request->filled('category_name')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->category_name . '%');
            });
        }

        if ($request->filled('in_stock')) {
            $query->where('in_stock', $request->boolean('in_stock'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Fetch users with pagination
        $products = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($perPage + 1) // Take one extra to check for next page
            ->get();

        // Check if there's a next page
        $hasNextPage = $products->count() > $perPage;
        if ($hasNextPage) {
            $products = $products->slice(0, $perPage);
        }

        // Format the users
        $formattedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'seller_id' => $product->seller_id,
                'category_id' => $product->category_id,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'sale_price' => $product->sale_price,
                'status' => $product->status,
                'sale_count' => $product->sale_count,
                'review_count' => $product->review_count,
                'image' => $this->fileService->multipleImageDisplaySingleImage($product->image, 'products') ?? [],
                'free_delivery' => $product->free_delivery,
                'delivery_fee' => $product->delivery_fee,
                'in_stock' => $product->in_stock,
                'badge' => $product->badge,
                'attributes' => $product->attributes ?? [],
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Return response with metadata
        return response()->json([
            'status' => 'success',
            'data' => $formattedProducts,
            'metadata' => [
                'per_page' => (int) $perPage,
                'current_page' => $page,
                'has_next_page' => $hasNextPage,
                'from' => $offset + 1,
                'to' => $offset + $products->count(),
            ]
        ], 200);
    }

    public function search(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * $perPage;

        $query = Product::query()->with(['category']);

        if ($request->has('category_name')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->input('category_name'));
            });
        }

        $products = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($perPage + 1)
            ->get();

        // Check for next page and adjust
        $hasNextPage = $products->count() > $perPage;
        if ($hasNextPage) {
            $products = $products->slice(0, $perPage);
        }

        // Format bank accounts
        $formattedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'seller_id' => $product->seller_id,
                'seller' => $product->seller->with('user'),
                'category_id' => $product->category_id,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'sale_price' => $product->sale_price,
                'status' => $product->status,
                'sale_count' => $product->sale_count,
                'review_count' => $product->review_count,
                'image' => $this->fileService->multipleDisplayUploadFile($product->image, 'products') ?? [],
                'free_delivery' => $product->free_delivery,
                'delivery_fee' => $product->delivery_fee,
                'in_stock' => $product->in_stock,
                'badge' => $product->badge,
                'attributes' => $product->attributes ?? [],
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedProducts,
            'metadata' => [
                'per_page' => (int) $perPage,
                'current_page' => $page,
                'has_next_page' => $hasNextPage,
                'from' => $offset + 1,
                'to' => $offset + $products->count(),
                'search_params' => [
                    'category_name' => $request->input('company_name', ''),
                ],
            ],
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
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $products = Product::findOrFail($id);
        $data = [
            'id' => $products->id,
            'name' => $products->name,
            'seller_id' => $products->seller_id,
            'seller' => $products->seller->with('user'),
            'category_id' => $products->category_id,
            'slug' => $products->slug,
            'description' => $products->description,
            'price' => $products->price,
            'stock' => $products->stock,
            'sale_price' => $products->sale_price,
            'status' => $products->status,
            'sale_count' => $products->sale_count,
            'review_count' => $products->review_count,
            'image' => $this->fileService->multipleDisplayUploadFile($products->image, 'products') ?? [],
            'free_delivery' => $products->free_delivery,
            'delivery_fee' => $products->delivery_fee,
            'in_stock' => $products->in_stock,
            'badge' => $products->badge,
            'attributes' => $products->attributes ?? [],
            'created_at' => $products->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $products->updated_at->format('Y-m-d H:i:s'),
        ];
        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => $data,
        ], 200 );
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
