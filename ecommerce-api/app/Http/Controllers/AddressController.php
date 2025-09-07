<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('api')->user();
        $address = Address::where('user_id', $user->id)->first();
        if (!$address) {
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => 'not found'
            ], 200);
        }
        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => $address,
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
        $request->validate([
            'address_line_1' => 'required|string',
            'address_line_2' => 'required|string',
            'city' => 'required|string',
            'stats' => 'required|string',
            'country_code' => 'required|String',
            'postal_code' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            Address::create([
                'user_id' => $user->id,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'city' => $request->city,
                'stats' => $request->stats,
                'country_code' => $request->country_code,
                'postal_code' => $request->postal_code,
            ]);

            DB::commit();
            return response()->json([
                'verified' => true,
                'status' => 'success',
                'message' => 'Address Created',
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
        $address = Address::findOrFail($id)->where('user_id', auth('api')->id());

        $request->validate([
            'address_line_1' => 'required|string',
            'address_line_2' => 'required   |string',
            'city' => 'required|string',
            'stats' => 'required|string',
            'country_code' => 'required|String',
            'postal_code' => 'required|string',
        ]);
        DB::beginTransaction();
        try {
            $address->update([
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'city' => $request->city,
                'stats' => $request->stats,
                'country_code' => $request->country_code,
                'postal_code' => $request->postal_code,
            ]);

            DB::commit();
            return response()->json([
                'verified' => true,
                'status' => 'success',
                'message' => 'updated successfully',
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $address = Address::findOrFail($id)->where('user_id', auth('api')->id());
        $address->delete();
        return response()->json([
            'verified' => true,
            'status' => 'success',
            'message' => 'deleted successfully',
        ], 200);
    }
}
