<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Service\FileService;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    private $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    public function banner()
    {
        //
    }

    public function topSaleToday()
    {

        $topSaleTodays = Product::with(['seller.user', 'category']) // eager load relations
            ->whereDate('created_at', Carbon::today())
            ->orderBy('sale_count', 'desc')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        // Transform the data into the required format
        $formattedTopSaleTodays = $topSaleTodays->map(function ($topSaleToday) {
            return [
                'id' => $topSaleToday->id,
                'name' => $topSaleToday->name,
                'seller_id' => $topSaleToday->seller_id,
                'category_id' => $topSaleToday->category_id,
                'slug' => $topSaleToday->slug,
                'description' => $topSaleToday->description,
                'price' => $topSaleToday->price,
                'sale_price' => $topSaleToday->sale_price ?? null,
                'sale_count' => $topSaleToday->sale_count ?? 0,
                'review_count' => $topSaleToday->review_count ?? 0,
                'image' => $this->fileService->imageDisplay($topSaleToday->image, 'product') ?? [],
                'free_delivery' => $topSaleToday->free_delivery,
                'delivery_fee' => $topSaleToday->delivery_fee,
                'in_stock' => $topSaleToday->in_stock,
                'badge' => $topSaleToday->badge,
                'attributes' => $topSaleToday->attributes,
                'created_at' => $topSaleToday->created_at,
            ];
        });

        // Return the paginated response with metadata
        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => $formattedTopSaleTodays,
        ]);
    }

    public function category()
    {
        $categories = Category::query();
        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => $categories,
        ], 200);
    }

    public function bestSellingProduct()
    {
        $bestSellingProducts = Product::with(['seller.user', 'category']) // eager load relations
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->orderBy('sale_count', 'desc')
            ->orderBy('id', 'desc')
            ->take(4)
            ->get();

        $formattedBestSellingProducts = $bestSellingProducts->map(function ($bestSellingProduct) {
            return [
                'id' => $bestSellingProduct->id,
                'name' => $bestSellingProduct->name,
                'seller_id' => $bestSellingProduct->seller_id,
                'category_id' => $bestSellingProduct->category_id,
                'slug' => $bestSellingProduct->slug,
                'description' => $bestSellingProduct->description,
                'price' => $bestSellingProduct->price,
                'sale_price' => $bestSellingProduct->sale_price ?? null,
                'sale_count' => $bestSellingProduct->sale_count ?? 0,
                'review_count' => $bestSellingProduct->review_count ?? 0,
                'image' => $this->fileService->multipleDisplayUploadFile($bestSellingProduct->image, 'products') ?? [],
                'free_delivery' => $bestSellingProduct->free_delivery,
                'delivery_fee' => $bestSellingProduct->delivery_fee,
                'in_stock' => $bestSellingProduct->in_stock,
                'badge' => $bestSellingProduct->badge,
                'attributes' => $bestSellingProduct->attributes,
                'created_at' => $bestSellingProduct->created_at,
            ];
        });


        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => $formattedBestSellingProducts,
        ]);
    }

    public function exploreOurProduct()
    {
        $exploreSellingProducts = Product::with(['seller.user', 'category']) // eager load relations
            ->orderBy('id', 'desc')
            ->take(16)
            ->get();


        // Transform the data into the required format
        $formattedExploreSellingProducts = $exploreSellingProducts->map(function ($exploreSellingProduct) {
            return [
                'id' => $exploreSellingProduct->id,
                'name' => $exploreSellingProduct->name,
                'seller_id' => $exploreSellingProduct->seller_id,
                'category_id' => $exploreSellingProduct->category_id,
                'slug' => $exploreSellingProduct->slug,
                'description' => $exploreSellingProduct->description,
                'price' => $exploreSellingProduct->price,
                'sale_price' => $exploreSellingProduct->sale_price ?? null,
                'sale_count' => $exploreSellingProduct->sale_count ?? 0,
                'review_count' => $exploreSellingProduct->review_count ?? 0,
                'image' => $this->fileService->imageDisplay($exploreSellingProduct->image, 'product') ?? [],
                'free_delivery' => $exploreSellingProduct->free_delivery,
                'delivery_fee' => $exploreSellingProduct->delivery_fee,
                'in_stock' => $exploreSellingProduct->in_stock,
                'badge' => $exploreSellingProduct->badge,
                'attributes' => $exploreSellingProduct->attributes,
                'created_at' => $exploreSellingProduct->created_at,
            ];
        });

        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => $formattedExploreSellingProducts,
        ]);
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
