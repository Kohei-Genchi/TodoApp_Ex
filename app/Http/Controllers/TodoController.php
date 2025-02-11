<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    // Get all todos
    public function index()
    {
        return Todo::orderBy('created_at', 'desc')->get();
    }

    // Create new todo
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $todo = Todo::create([
            'title' => $request->title,
            'completed' => false,
        ]);

        return $todo;
    }

    // Update todo status
    public function update(Request $request, Todo $todo)
    {
        $todo->update([
            'completed' => $request->completed
        ]);

        return $todo;
    }

    // Delete todo
    public function destroy(Todo $todo)
    {
        $todo->delete();
        return response()->json(['message' => 'Todo deleted']);
    }
}
