<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::with('role')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->email_verify != true) {
            return response()->json(['verified' => false, 'status' =>  'error', 'message' => 'Email is not verified'], 401);
        }

        if ($user->status == 1) {
            return response()->json(['verified' => false, 'status' =>  'error', 'message' => 'Account have been banned']);
        }

        Auth::guard('api')->setUser($user);
        $token = Auth::guard('api')->tokenById($user->id);

        return $this->respondWithToken($token);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = auth('api')->user()->load('role');

        $extraData = [];

        switch ($user->role->name) {
            case 'buyer':
                $extraData['buyer'] = Buyer::where('user_id', $user->id)->first();
                break;

            case 'seller':
                $extraData['seller'] = Seller::where('user_id', $user->id)->first();
                break;

            case 'admin':
            default:
                // no extra data for admin or other roles
                break;
        }

        return response()->json([
            'verified' => true,
            'status' => 'success',
            'message' => 'login successful',
            'data' => array_merge([
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'role' => $user->role,
                    'image' => $user->image,
                    'phone' => $user->phone,
                ],
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ], $extraData)
        ], 200);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth('api')->user()->load('role');
        $extraData = [];

        switch ($user->role->name) {
            case 'buyer':
                $extraData['buyer'] = Buyer::where('user_id', $user->id)->first();
                break;

            case 'seller':
                $extraData['seller'] = Seller::where('user_id', $user->id)->first();
                break;

            case 'admin':
            default:
                // no extra data for admin or other roles
                break;
        }

        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => array_merge([
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'role' => $user->role,
                    'image' => $user->image,
                    'phone' => $user->phone,
                ]
            ], $extraData)
        ], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Register a User.
     *
     * @param  Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:100',
            'gender' => 'required|string',
            'email' => 'required|string|email|max:200|unique:users',
            'password' => 'required|string|min:8|max:16|confirmed',
            'dob'   => 'required|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'phone' => 'required|string|max:15',
        ]);

        $roles = Role::where('name', 'buyer')->first();
        if (!$roles) {
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => 'Role not found'
            ], 404);
        }
        DB::beginTransaction();
        try {
            $fileUploadService = new \App\Http\Controllers\Service\FileService();
            $imageName = null;
            if ($request->hasFile('image')) {
                $imageName = $fileUploadService->uploadFile($request->file('image'), 'users', null);
            }
            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
                'email_verify' => true,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'image' => $imageName,
                'phone' => $request->phone,
                'status' => 'active',
                'role_id' => $roles->id,
            ]);

            $buyer = Buyer::create([
                'user_id' => $user->id,
                'loyalty_points' => 0,
            ]);

            $token = auth('api')->login($user);
            DB::commit();
            return response()->json([
                'verified' => true,
                'status' => 'success',
                'message' => 'Registered successfully',
                'data' => [
                    'user' => $user,
                    'buyer' => $buyer,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ]
            ], 201);
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
