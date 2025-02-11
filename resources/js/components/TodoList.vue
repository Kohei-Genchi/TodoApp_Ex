<!-- resources/js/components/TodoList.vue -->
<template>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
      <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">My Todo List</h1>

      <!-- Add Todo Form -->
      <div class="mb-6">
        <form @submit.prevent="addTodo" class="flex gap-2">
          <input
            v-model="newTodo"
            type="text"
            placeholder="Add a new todo..."
            class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
          >
          <button
            type="submit"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition"
          >
            Add
          </button>
        </form>
      </div>

      <!-- Todo List -->
      <div class="space-y-3">
        <div
          v-for="todo in todos"
          :key="todo.id"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex items-center gap-3">
            <input
              type="checkbox"
              :checked="todo.completed"
              @change="toggleTodo(todo)"
              class="w-5 h-5 text-blue-500"
            >
            <span :class="{ 'line-through text-gray-400': todo.completed }">
              {{ todo.title }}
            </span>
          </div>
          <button
            @click="deleteTodo(todo.id)"
            class="text-red-500 hover:text-red-600"
          >
            Delete
          </button>
        </div>
      </div>

      <!-- Todo Stats -->
      <div class="mt-6 text-sm text-gray-600">
        <p>Total todos: {{ todos.length }}</p>
        <p>Completed: {{ completedCount }}</p>
      </div>
    </div>
  </template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../api'

const todos = ref([])
const newTodo = ref('')
const completedCount = computed(() => todos.value.filter(todo => todo.completed).length)

// Fetch all todos
const fetchTodos = async () => {
  try {
    const response = await api.get('/api/todos')  // Add leading slash
    todos.value = response.data
  } catch (error) {
    console.error('Error fetching todos:', error)
  }
}


// Add new todo
const addTodo = async () => {
  if (!newTodo.value.trim()) return
  try {
    const response = await api.post('/api/todos', {  // Add leading slash
      title: newTodo.value
    })
    todos.value.unshift(response.data)
    newTodo.value = ''
  } catch (error) {
    console.error('Error adding todo:', error)
  }
}

// Toggle todo completion
const toggleTodo = async (todo) => {
  try {
    const response = await api.put(`/api/todos/${todo.id}`, {  // Add leading slash
      completed: !todo.completed
    })
    const index = todos.value.findIndex(t => t.id === todo.id)
    todos.value[index] = response.data
  } catch (error) {
    console.error('Error updating todo:', error)
  }
}

// Delete todo
const deleteTodo = async (id) => {
  try {
    await api.delete(`/api/todos/${id}`)
    todos.value = todos.value.filter(todo => todo.id !== id)
  } catch (error) {
    console.error('Error deleting todo:', error)
  }
}

// Load todos when component mounts
onMounted(fetchTodos)
</script>
