<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\RequestAsSeller;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RequestAsSellerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * $perPage;

        $requestAsSellers = RequestAsSeller::with('user')->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($perPage + 1)
            ->get();

        $hasNextPage = $requestAsSellers->count() > $perPage;
        if ($hasNextPage) {
            $requestAsSellers = $requestAsSellers->take($perPage);
        }

        // Transform the data into the required format
        $formattedRequestAsSellers = $requestAsSellers->map(function ($requestAsSeller) {
            return [
                'id' => $requestAsSeller->id,
                'user_id' => $requestAsSeller->user_id,
                'full_name' => $requestAsSeller->user ? $requestAsSeller->user->full_name : null,
                'gender' => $requestAsSeller->user ? $requestAsSeller->user->gender : null,
                'dob' => $requestAsSeller->user ? $requestAsSeller->user->dob : null,
                'email' => $requestAsSeller->user ? $requestAsSeller->user->email : null,
                'image' => $requestAsSeller->user ? $requestAsSeller->user->image : null,
                'phone' => $requestAsSeller->user ? $requestAsSeller->user->phone : null,
                'status' => $requestAsSeller->user ? $requestAsSeller->user->status : null,
                'description' => $requestAsSeller->description,
                'request_date' => $requestAsSeller->request_date,
                'pending_status' => $requestAsSeller->pending_status,
            ];
        });

        // Return the paginated response with metadata
        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => $formattedRequestAsSellers,
            'metadata' => [
                'per_page' => $perPage,
                'current_page' => $page,
                'has_next_page' => $hasNextPage,
                'from' => $offset + 1,
                'to' => $offset + $requestAsSellers->count(),
            ],
        ]);
    }


    /**
     * approval.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approval(string $id)
    {
        $requestAsSeller = RequestAsSeller::findOrFail($id);
        $user = User::where('id', $requestAsSeller->user_id)->first();
        $role = Role::where('name', 'seller')->first();
        $buyer = Buyer::where('user_id', $requestAsSeller->user_id)->first();

        DB::beginTransaction();
        try {
            Seller::create([
                'user_id' => $requestAsSeller->user_id,
                'rating' => 0,
                'status' => 1,
                'balance' => 0,
            ]);
            $user->update([
                'role_id' => $role->id,
            ]);
            $requestAsSeller->update(['pending_status' => 'approved']);
            if ($buyer) {
                $buyer->delete();
            }
            DB::commit();
            return response()->json(
                [
                    'verified' => true,
                    'status' => 'success',
                    'message' => 'Seller approved successfully.'
                ],
                200
            );
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
     * reques seller.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejected(string $id)
    {
        $requestAsSeller = RequestAsSeller::findOrFail($id);
        $requestAsSeller->update(['pending_status' => 'rejected']);
        return response()->json([
            'verified' => true,
            'status' => ' success',
            'message' => 'Request As Seller Have Been Rejected',
        ], 200);
    }

    /**
     * reques seller.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestAsSeller(Request $request)
    {
        $user = auth('api')->user();
        $isSeller = Seller::where('user_id', $user->id)->first();
        if ($isSeller) {
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => 'Account is already a seller',
            ]);
        }
        
        $request->validate([
            'description' => 'required|string',
        ]);

        RequestAsSeller::create([
            'user_id' => $user->id,
            'description' => $request->description,
            'request_date' => now(),
            'pending_status' => 'pending',
        ]);

        return response()->json([
            'verified' => true,
            'status' => ' success',
            'message' => 'Send Request',
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
        $requestAsSeller = RequestAsSeller::findOrFail($id);
        $requestAsSeller->delete();
        return response()->json([
            'verified' => true,
            'status' => 'error',
            'message' => 'Request Seller have been remove successfully',
        ], 200);
    }
}
