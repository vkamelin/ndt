<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Реестр {{ $register->number ?? '' }}</title>
</head>
<body>
    <h1>Реестр {{ $register->number ?? '' }}</h1>
    <p>Тип: {{ $register->type?->name ?? '' }}</p>
    <p>Дата: {{ $register->date?->format('d.m.Y') ?? '' }}</p>
    <p>Статус: {{ $register->status?->label() ?? '' }}</p>
</body>
</html>
