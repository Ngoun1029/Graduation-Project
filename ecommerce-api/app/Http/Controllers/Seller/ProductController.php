<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Service\FileService;
use App\Models\Product;
use App\Models\Role;
use App\Models\Seller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $user = auth('api')->user();

        $seller = Seller::where('user_id', $user->id)->first();

        if (!$seller) {
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => 'forbidden',
            ], 401);
        }

        // // Start building the query with relationships
        $query = Product::with('category')->where('seller_id', $user->seller->id);

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
                'category_id' => $product->category_id,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'sale_price' => $product->sale_price,
                'status' => $product->status,
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

    /**
     * Display a listing of the resource with search.
     */
    public function search(Request $request)
    {
        // Get pagination parameters
        $perPage = $request->query('per_page', 15);

        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * $perPage;

        $user = auth('api')->user();
        $seller = Seller::where('user_id', $user->id)->first();

        $query = Product::query()->where('seller_id', $seller->id);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%")
                    ->orWhereHas('category', function ($cat) use ($search) {
                        $cat->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('in_stock')) {
            $query->where('in_stock', $request->in_stock);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->skip($offset)->take($perPage + 1)->get();
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

        return response()->json([
            'status' => 'success',
            'data' => $formattedProducts,
            'metadata' => [
                'per_page' => (int) $perPage,
                'current_page' => $page,
                'has_next_page' => $hasNextPage,
                'from' => $offset + 1,
                'to' => $offset + $products->count(),
                'search' => $request->input('search', ''),
                'filter_param' => [
                    'category_id' => $request->input('category_id', ''),
                    'in_stock' => $request->input('in_stock', ''),
                    'min_price' => $request->input('min_price', ''),
                    'max_price' => $request->input('max_price', ''),
                ]
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
        $user = auth('api')->user();
        $seller = Seller::where('user_id', $user->id)->first();

        if (!$seller) return response()->json(['verified' => false, 'status' => 'error', 'message' => 'forbidden']);

        $request->validate([
            'category_id'   => 'required|integer',
            'name'          => 'required|string',
            'description'   => 'required|string',
            'price'         => 'required|decimal:0,2',
            'stock'         => 'required|integer',
            'sale_price'    => 'nullable|decimal:0,2',
            'status'        => 'required|string',
            'free_delivery' => 'required|boolean',
            'delivery_fee'  => 'nullable|decimal:0,2',
            'in_stock'      => 'required|boolean',
            'image'         => 'required',
            'image.*'       => 'file|mimes:jpeg,png,gif,mp4|max:20000',
            'color'   => 'nullable|array',
            'color.*' => 'string',
            'size'    => 'nullable|array',
            'size.*'  => 'string',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::create([
                'category_id'   => $request->category_id,
                'seller_id'     => $seller->id,
                'name'          => $request->name,
                'slug'          => Str::slug($request->name),
                'description'   => $request->description,
                'price'         => $request->price,
                'stock'         => $request->stock,
                'sale_price'    => $request->sale_price,
                'status'        => $request->status,
                'sale_count'    => 0,
                'review_count'  => 0,
                'image' => [],
                'free_delivery' => $request->free_delivery,
                'delivery_fee'  => $request->delivery_fee,
                'in_stock'      => $request->in_stock,
                'badge'         => 'none',
                'attributes'    => [
                    'color' => $request->color ?? [],
                    'size'  => $request->size ?? [],
                ],
            ]);

            $images = $this->fileService->multipleUploadFile($request, 'image', 'products', $seller->user->id);
            $product->update(['image' => $images]);

            DB::commit();
            return response()->json([
                'verified' => true,
                'status'   => 'success',
                'message'  => 'Product created successfully',
                'data'     => $product,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'verified' => false,
                'status'   => 'error',
                // 'message'  => Str::limit($e->getMessage(), 150, '...'),
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $products = Product::findOrFail($id);
        $data = [
            'id' => $products->id,
            'name' => $products->name,
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
            'data' => $data
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
        $user = auth('api')->user();
        $seller = Seller::where('user_id', $user->id)->first();
        if (!$seller) return response()->json(['verified' => false, 'status' => 'error', 'message' => 'forbidden'], 403);
        $product = Product::where('seller_id', $seller->id)->first();
        if (!$product) return response()->json(['verified' => false, 'status' => 'error', 'message' => 'not found'], 404);
        $validate = $request->validate([
            'category_id'   => 'required|integer',
            'name'          => 'required|string',
            'description'   => 'required|string',
            'price'         => 'required|decimal:0,2',
            'stock'         => 'required|integer',
            'sale_price'    => 'nullable|decimal:0,2',
            'status'        => 'required|string',
            'free_delivery' => 'required|boolean',
            'delivery_fee'  => 'nullable|decimal:0,2',
            'in_stock'      => 'required|boolean',
            'image'         => 'required',
            'image.*'       => 'file|mimes:jpeg,png,gif,mp4|max:20000',
            'old_image'     => 'nullable',
            'old_image.*'   => 'file|mimes:jpeg,png,gif|max:20000',
            'color'   => 'nullable|array',
            'color.*' => 'string',
            'size'    => 'nullable|array',
            'size.*'  => 'string',

        ]);


        DB::beginTransaction();
        try {
            if ($request->validate($validate)) {
                $product->update([
                    'category_id' => $request->category_id,
                    'name' => $request->name,
                    'description' => $request->description,
                    'price' => $request->price,
                    'stock' => $request->stock,
                    'sale_price' => $request->sale_price,
                    'status' => $request->status,
                    'free_delivery' => $request->free_delivery,
                    'delivery_fee' => $request->delivery_fee,
                    'in_stock' => $request->in_stock,
                ]);
            }
            if ($request->hasFile('image')) {
            }



            DB::commit();
            return response()->json([
                'verified' => true,
                'status' => 'success',
                'message' => 'Update successfully',
                'data' => $product,
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => Str::limit($e->getMessage(), 150, '...')
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth('api')->user();
        $seller = Seller::where('user_id', $user->id)->first();
        if (!$seller) return response()->json(['verified' => false, 'status' => 'error', 'message' => 'forbidden'], 403);
        $product = Product::where('seller_id', $seller->id)->first();
        if (!$product) return response()->json(['verified' => false, 'status' => 'error', 'message' => 'not found'], 404);
        DB::beginTransaction();
        try {
            if (!empty($product->image)) {
                foreach ($product->image as $storedPath) { // values are actual saved paths
                    $path = public_path($storedPath);
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }
            $product->delete();
            DB::commit();
            return response()->json([
                'verified' => true,
                'status' => 'success',
                'message' => 'Product Delete Successfully',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => Str::limit($e->getMessage(), 150, '...'),
            ], 500);
        }
    }
}
