<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Service\FileService;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;

class SellerProfileController extends Controller
{
    private $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    public function sellerProfile(string $id)
    {
        $seller = Seller::where('seller_id')->with('user')->first();
        if (!$seller) {
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => 'not found',
            ], 404);
        }
        return response()->json([

            'verified' => true,
            'status' => 'success',
            'data' => $seller,
        ], 200);
    }

    public function sellerProfileProductList(Request $request, string $id)
    {
        // Get per_page from query parameter, restrict to 5, 15, 30
        $perPage = $request->query('per_page', 15);
        if (!in_array($perPage, [5, 15, 30])) {
            $perPage = 15;
        }
        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * $perPage;

        // // Start building the query with relationships
        $query = Product::with('category')->where('seller_id', $id);

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
        if ($hasNextPage)  $products = $products->slice(0, $perPage);

        // Format the users
        $formattedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category_name' => $product->category->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'sale_price' => $product->sale_price,
                'sale_count' => $product->sale_count,
                'review_count' => $product->review_count,
                'image' => $this->fileService->multipleImageDisplaySingleImage($product->image) ?? [],
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
                'search_params' => [
                    'category_name' => $request->input('category_name', ''),
                    'name' => $request->input('name', ''),,
                    'in_stock' => $request->input('in_stock', ''),
                    'min_price' => $request->input('min_price', ''),
                    'max_price' => $request->input('max_price', ''),
                ],
            ]
        ], 200);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
