<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale = 1.0">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <title>@yield('title', '废文网') - 每日一丧</title>
      <link rel="stylesheet" href="/css/app.css">
   </head>
   <body>
         @include('layouts._header')
         @include('shared.messages')
         @yield('content')
         @include('layouts._footer')
         <script src="/js/app.js"></script>
         <script src="/js/marked/lib/marked.min.js"></script>
         <script src="/js/sosad.js"></script>
   </body>
</html>
