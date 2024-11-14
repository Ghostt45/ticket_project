<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Моё приложение')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<!-- Главное меню -->
<nav>
    <ul>
        <li><a href="/">Главная</a></li>
        <li><a href="{{ route('orders.bookForm') }}">Бронирование</a></li>
    </ul>
</nav>

<!-- Основной контент -->
<div class="container">
    @yield('content')
</div>

<!-- Подключение JavaScript -->
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
