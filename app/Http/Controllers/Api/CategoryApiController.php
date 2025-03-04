<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryApiController extends Controller
{
    /**
     * Get all categories for the authenticated user.
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                // Create a default user for debugging
                $user = User::firstOrCreate(
                    ['email' => 'guest@example.com'],
                    [
                        'name' => 'Guest User',
                        'password' => bcrypt('password'),
                    ]
                );
            }

            // Ensure user is not null before calling categories()
            if ($user) {
                $categories = $user->categories()->orderBy('name')->get();
                return response()->json($categories);
            } else {
                // This should not happen, but just in case
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or could not be created'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created category via AJAX.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get the authenticated user or create a default one
            $user = Auth::user();

            if (!$user) {
                // Create a default user for debugging
                $user = User::firstOrCreate(
                    ['email' => 'guest@example.com'],
                    [
                        'name' => 'Guest User',
                        'password' => bcrypt('password'),
                    ]
                );
            }

            // Create the category
            $category = Category::create([
                'name' => $request->name,
                'color' => $request->color,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        // Check if the authenticated user owns this category
        if ($category->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update the category
            $category->update([
                'name' => $request->name,
                'color' => $request->color
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a category.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check if the authenticated user owns this category
        if ($category->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            // Delete the category
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
