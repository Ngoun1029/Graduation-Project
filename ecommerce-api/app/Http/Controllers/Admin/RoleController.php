<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * $perPage;

        $roles = Role::orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($perPage + 1)
            ->get();

        $hasNextPage = $roles->count() > $perPage;
        if ($hasNextPage) {
            $roles = $roles->take($perPage);
        }

        // Transform the data into the required format
        $formattedRoles = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'created_at' => $role->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $role->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Return the paginated response with metadata
        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => $formattedRoles,
            'metadata' => [
                'per_page' => $perPage,
                'current_page' => $page,
                'has_next_page' => $hasNextPage,
                'from' => $offset + 1,
                'to' => $offset + $roles->count(),
            ],
        ]);
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

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string',
        ]);
        try {
            $role = Role::create($validated);

            return response()->json([
                'verified' => true,
                'status' => 'success',
                'message' => 'Role created successfully',
                'data' => $role
            ], 201);
        } catch (Exception $e) {
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
        $role = Role::findOrFail($id);
        return response()->json([
            'verified' => true,
            'status' => 'success',
            'data' => $role
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
        try {
            $roles = Role::findOrFail($id);
            if (!$roles) {
                return response()->json([
                    'verified' => false,
                    'status' => 'error',
                    'message' => 'Role not found',
                ], 404);
            }
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $roles->id,
                'description' => 'nullable|string',
            ]);

            $roles->update($validated);
            return response()->json([
                'verified' => true,
                'status' => 'success',
                'message' => 'Role updated successfully',
                'data' => $roles
            ], 200);
        } catch (Exception $e) {
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
        try {
            $role = Role::findOrFail($id);
            if (!$role) {
                return response()->json([
                    'verified' => false,
                    'status' => 'error',
                    'message' => 'Role not found',
                ], 404);
            }
            $role->delete();

            return response()->json([
                'verified' => true,
                'status' => 'success',
                'message' => 'Role deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => Str::limit($e->getMessage(), 150, '...'),
            ], 500);
        }
    }
}
