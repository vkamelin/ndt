<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Заключение {{ $conclusion->number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 12px;
        }

        .muted {
            color: #6b7280;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Заключение {{ $conclusion->number }}</h1>
    <div class="muted">Дата: {{ $conclusion->date?->format('d.m.Y') }}</div>
    <div class="muted">Объект: {{ $conclusion->object?->name }}</div>
    <div class="muted">Метод: {{ $conclusion->method?->name }}</div>
    <div class="muted">Статус: {{ $conclusion->status->label() }}</div>

    <table>
        <thead>
            <tr>
                <th>№</th>
                <th>Стык</th>
                <th>Результат</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($conclusion->items as $item)
                <tr>
                    <td>{{ $item->sort_order }}</td>
                    <td>{{ $item->result?->weld?->weld_number }}</td>
                    <td>{{ $item->result?->status->label() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
