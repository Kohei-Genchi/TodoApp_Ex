<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryApiController extends Controller
{
    /**
     * Get all categories for the authenticated user.
     */
    public function index(): JsonResponse
    {
        try {
            // For unauthenticated access, return empty array instead of 401
            if (!Auth::check()) {
                Log::info('Unauthenticated access to categories API - returning empty array');
                return response()->json([]);
            }

            $user = Auth::user();
            Log::info('Fetching categories for user ID: ' . $user->id);

            $categories = $user->categories()->orderBy('name')->get();

            // Log the fetched categories
            Log::info('Fetched ' . count($categories) . ' categories for user');
            Log::debug('Categories data: ' . json_encode($categories));

            return response()->json($categories);

        } catch (\Exception $e) {
            Log::error('Error in CategoryApiController->index: ' . $e->getMessage());
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
        // For unauthenticated access, return empty array instead of 401
        if (!Auth::check()) {
            Log::info('Unauthenticated access to category store API - returning empty array');
            return response()->json([]);
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
            $user = Auth::user();

            // Create the category
            $category = Category::create([
                'name' => $request->name,
                'color' => $request->color,
                'user_id' => $user->id
            ]);

            Log::info('Created new category ID: ' . $category->id . ' for user ID: ' . $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error in CategoryApiController->store: ' . $e->getMessage());
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
        // For unauthenticated access, return empty array instead of 401
        if (!Auth::check()) {
            Log::info('Unauthenticated access to category update API - returning empty array');
            return response()->json([]);
        }

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
            Log::error('Error in CategoryApiController->update: ' . $e->getMessage());
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
        // For unauthenticated access, return empty array instead of 401
        if (!Auth::check()) {
            Log::info('Unauthenticated access to category destroy API - returning empty array');
            return response()->json([]);
        }

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
            Log::error('Error in CategoryApiController->destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
