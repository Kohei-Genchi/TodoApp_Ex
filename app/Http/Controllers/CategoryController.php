<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;


class CategoryController extends Controller
{

    // Show all categories
    public function index(): View
    {
        $categories = Auth::user()->categories()->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    // Create a new category
    public function store(CategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        Auth::user()->categories()->create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'カテゴリーを追加しました');
    }

    // Update an existing category
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);
        $category->update($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'カテゴリーを更新しました');
    }

    // Delete a category
    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'カテゴリーを削除しました');
    }
}
