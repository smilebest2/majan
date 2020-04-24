<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>majan</title>
        <link rel="stylesheet" href="{{ asset('/css/test.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/scss.css') }}">
        <script src="{{asset('/js/jquery-3.5.0.min.js')}}"></script>
        <script>
        $(function(){
            setInterval(function(){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ action('TestController@readycheck') }}",
                type: 'POST',
                dataType:'json'
            })
            // Ajaxリクエストが成功した場合
            .done(function(data) {
                if (data.result == "OK") {
                    window.location.href = "<?php echo url('') . "/start"; ?>";
                }
            })
            // Ajaxリクエストが失敗した場合
            .fail(function(data) {
                alert("接続失敗");
            });
        },5000);
});

        </script>
    </head>
    <body>
        <div id="load">
            <div>！</div>
            <div>中</div>
            <div>付</div>
            <div>受</div>
            <div>者</div>
            <div>加</div>
            <div>参</div>
        </div>
    </body>
</html>
