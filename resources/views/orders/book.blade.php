@extends('layouts.app')

@section('title', 'Бронирование билетов')

@section('content')
    <h2>Бронирование заказа</h2>

    <!-- Форма для бронирования заказа -->
    <form method="POST" action="{{ route('orders.book') }}">
        @csrf

        <!-- Поле для выбора события -->
        <div>
            <label for="event_id">Событие:</label>
            <select name="event_id" id="event_id" required>
                <option value="">Выберите событие</option>
                @foreach($events as $event)
                    <option value="{{ $event->id }}">{{ $event->name }} ({{ $event->date }})</option>
                @endforeach
            </select>
        </div>

        <!-- Поле для даты события -->
        <div>
            <label for="event_date">Дата события:</label>
            <input type="date" name="event_date" id="event_date" required>
        </div>

        <!-- Поля для взрослого билета -->
        <div>
            <h4>Взрослый билет</h4>
            <label for="ticket_adult_price">Цена билета:</label>
            <input type="number" step="0.01" name="ticket_adult_price" id="ticket_adult_price" required>

            <label for="ticket_adult_quantity">Количество билетов:</label>
            <input type="number" name="ticket_adult_quantity" id="ticket_adult_quantity" min="0" required>
        </div>

        <!-- Поля для детского билета -->
        <div>
            <h4>Детский билет</h4>
            <label for="ticket_kid_price">Цена билета:</label>
            <input type="number" step="0.01" name="ticket_kid_price" id="ticket_kid_price" required>

            <label for="ticket_kid_quantity">Количество билетов:</label>
            <input type="number" name="ticket_kid_quantity" id="ticket_kid_quantity" min="0" required>
        </div>

        <!-- Кнопка отправки формы -->
        <button type="submit">Забронировать заказ</button>
    </form>

    <!-- Отображение списка заказов -->
    <h3>Список заказов</h3>
    <table>
        <thead>
        <tr>
            <th>Штрих-код</th>
            <th>Дата события</th>
            <th>Цена</th>
            <th>Взрослые билеты</th>
            <th>Детские билеты</th>
        </tr>
        </thead>
        <tbody>
        @foreach($orders as $order)
            <tr>
                <td>{{ $order->barcode }}</td>
                <td>{{ $order->event_date }}</td>
                <td>{{ $order->equal_price }}</td>
                <td>{{ $order->ticket_adult_quantity }}</td>
                <td>{{ $order->ticket_kid_quantity }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
