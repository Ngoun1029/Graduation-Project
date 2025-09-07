<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\RequestAsSeller;
use App\Models\Seller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function dashboard()
    {
        // --- KPI Cards ---
        $totalUsers   = User::count();
        $newUsersThisWeek = User::where('created_at', '>=', Carbon::now()->subWeek())->count();

        $totalBuyers  = Buyer::count();
        $totalSellers = Seller::count();
        $pendingSellerRequests = RequestAsSeller::where('pending_status', 'pending')->count();

        $bannedUsers  = User::where('status', 1)->count();
        $unbannedUsers = User::where('status', 0)->count();

        // --- Charts ---
        // User Growth (last 6 weeks)
        $userGrowth = User::select(
            DB::raw("DATE_TRUNC('week', created_at) as week"),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subWeeks(6))
            ->groupBy('week')
            ->orderBy('week')
            ->pluck('count', 'week');

        // Buyer Registrations (last 6 months)
        $buyerGrowth = Buyer::select(
            DB::raw("DATE_TRUNC('month', created_at) as month"),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');


        // Seller Request Status
        $sellerRequestStatus = RequestAsSeller::select('pending_status', DB::raw('COUNT(*) as count'))
            ->groupBy('pending_status')
            ->pluck('count', 'pending_status');

        // --- Recent Activities ---
        $recentUsers = User::latest()->take(5)->get(['id', 'full_name', 'email', 'created_at']);
        $recentRequests = RequestAsSeller::latest()->take(5)->get(['id', 'user_id', 'description', 'pending_status', 'created_at']);

        return response()->json([
            'verified' => true,
            'status'   => 'success',
            'data'     => [
                'users' => [
                    'total' => $totalUsers,
                    'new_this_week' => $newUsersThisWeek,
                    'banned' => $bannedUsers,
                    'unbanned' => $unbannedUsers,
                ],
                'buyers' => [
                    'total' => $totalBuyers,
                ],
                'sellers' => [
                    'total' => $totalSellers,
                    'pending_requests' => $pendingSellerRequests,
                ],
                'charts' => [
                    'user_growth' => $userGrowth,
                    'buyer_growth' => $buyerGrowth,
                    'seller_request_status' => $sellerRequestStatus,
                ],
                'recent' => [
                    'users' => $recentUsers,
                    'requests' => $recentRequests,
                ]
            ],
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
