<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            window.Laravel = {!! json_encode([
                'user' => Auth::check() ? [
                    'id' => Auth::id(),
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ] : null,
                'csrfToken' => csrf_token(),
                'apiUrl' => url('/api'),
                'baseUrl' => url('/'),
                'currentView' => request()->query('view', 'today'),
                'currentDate' => request()->query('date', now()->format('Y-m-d'))
            ]) !!};
        </script>
    </head>
    <body class="font-sans antialiased">
        <!-- Hidden logout form (used by Vue components) -->
        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
            @csrf
        </form>

        <!-- Vue App Mount Point -->
        <div id="app">
            <!-- Vue will render here -->
        </div>

        <!-- Support Scripts for TodoApp -->
        <script>
            // Function to handle task editing (for backward compatibility)
            function editTodo(taskIdOrData, todoData) {
                // This will be overridden by the implementation in app.js
                console.log('editTodo called:', taskIdOrData, todoData);
            }

            // Function to handle trash action
            function trashMemo(id) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/todos/${id}/trash`;

                // Add CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                // Add method override for PATCH
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PATCH';
                form.appendChild(methodInput);

                // Submit the form
                document.body.appendChild(form);
                form.submit();
            }

            // Confirmation function for logout
            function confirmLogout() {
                if (confirm('ログアウトしてもよろしいですか？')) {
                    document.getElementById('logout-form').submit();
                }
            }
        </script>
    </body>
</html>
