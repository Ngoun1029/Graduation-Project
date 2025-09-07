<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Service\FileService;
use App\Models\Buyer;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class ProfileController extends Controller
{

    private $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    public function profile()
    {
        $user = auth('api')->user();
        $buyer = Buyer::where('user_id', $user->id)->with('user', 'user.role')->first();
        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => $buyer,
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

    public function update(Request $request)
    {
        $user = User::findOrFail(auth('api')->id());

        // validation rules (only validate fields that are present)
        $request->validate([
            'full_name' => 'sometimes|required|string',
            'email'     => 'sometimes|required|email|string',
            'gender'    => 'sometimes|required|string',
            'phone'     => 'sometimes|required|string',
            'dob'       => 'sometimes|required|string',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // update only provided fields
            $user->fill($request->only([
                'full_name',
                'email',
                'gender',
                'phone',
                'dob'
            ]));

            // handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $this->fileService->uploadFile(
                    $request->file('image'),
                    'users',
                    null
                );
                $user->image = $imagePath;
            }

            $user->save();
            DB::commit();

            return response()->json([
                'verified' => true,
                'status'   => 'success',
                'message'  => 'Updated Successfully',
                'data'     => $user->fresh(),
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'verified' => false,
                'status'   => 'error',
                'message'  => Str::limit($e->getMessage(), 150, '...'),
            ], 500);
        }
    }

    // public function update(Request $request)
    // {
    //     $user = User::findOrFail(auth('api')->id());

    //     // validation rules (apply for both JSON + form-data)
    //     $rules = [
    //         'full_name' => 'sometimes|required|string',
    //         'email'     => 'sometimes|required|email|string',
    //         'gender'    => 'sometimes|required|string',
    //         'phone'     => 'sometimes|required|string',
    //         'dob'       => 'sometimes|required|string',
    //         'image'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //     ];

    //     $request->validate($rules);

    //     DB::beginTransaction();
    //     try {
    //         // Update only the fields that are present (works for JSON or form-data)
    //         $user->fill($request->only([
    //             'full_name',
    //             'email',
    //             'gender',
    //             'phone',
    //             'dob'
    //         ]));

    //         // Handle file upload (form-data only)
    //         if ($request->hasFile('image')) {
    //             $imagePath = $this->fileService->uploadFile(
    //                 $request->file('image'),
    //                 'users',
    //                 null
    //             );
    //             $user->image = $imagePath;
    //         }

    //         $user->save();
    //         DB::commit();

    //         return response()->json([
    //             'verified' => true,
    //             'status'   => 'success',
    //             'message'  => 'Updated Successfully',
    //             'data'     => $user->fresh(),
    //         ], 200);
    //     } catch (Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'verified' => false,
    //             'status'   => 'error',
    //             'message'  => Str::limit($e->getMessage(), 150, '...'),
    //         ], 500);
    //     }
    // }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
