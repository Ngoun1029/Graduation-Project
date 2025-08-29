<?php

namespace App\Http\Controllers\Seller;

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


    public function sellerRequest(Request $request, $id)
    {

        $users = User::findOrFail($id);

        $request->validate([
            'shop_name' => 'required|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            Seller::create([
                'user_id' => $users->id,
                'shop_name' => $request->shop_name,
                'store_slug' => Str::slug($request->shop_name),
                'verification_status' => 0,
                'rating' => 0,
                'balance' => 0,
                'status' => 1,
            ]);
            DB::commit();
            return response()->json([
                'verified' => true,
                'status' => 'success',
                'message' => 'Seller request approved successfully.'

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
