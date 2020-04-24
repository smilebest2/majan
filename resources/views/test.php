<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>majan</title>
        <link rel="stylesheet" href="{{ asset('/css/test.css') }}">
        <script src="{{ asset('/js/jquery-3.5.0.min.js') }}"></script>
    </head>
    <body>
    
        {{$hello}}hoge
    </body>
</html>
