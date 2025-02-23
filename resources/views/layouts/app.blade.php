<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Todo</title>
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <div class="header-utilities">
            <a href="/" class="header__logo">Todo</a>
            <nav>
              <ul class="header-nav">
                <li class="header-nav__item">
                  <a class="header-nav__link" href="/categories">カテゴリ一覧</a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
