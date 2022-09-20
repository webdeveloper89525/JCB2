<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" value="{{ csrf_token() }}"/>
    <title>JCB</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdn.metroui.org.ua/v4/css/metro-icons.css">

    <link href="{{ mix('css/app.css') }}?{{time()}}" type="text/css" rel="stylesheet"/>
    <style>
        .bg-light {
            background-color: #eae9e9 !important;
        }
    </style>
</head>
<body>
<div id="app"></div>
<script src="{{ mix('js/app.js') }}?{{time()}}" type="text/javascript"></script>
</body>
</html>