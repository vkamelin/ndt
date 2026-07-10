<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Акт {{ $act->number ?? '' }}</title>
</head>
<body>
    <h1>Акт {{ $act->number ?? '' }}</h1>
    <p>Тип: {{ $act->type?->name ?? '' }}</p>
    <p>Дата: {{ $act->date?->format('d.m.Y') ?? '' }}</p>
</body>
</html>
