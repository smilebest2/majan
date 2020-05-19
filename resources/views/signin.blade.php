<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>majan</title>
        <link rel="stylesheet" href="{{ asset('/css/login.css') }}">
        <script src="{{asset('/js/jquery-3.5.0.min.js')}}"></script>
        <script>
        var play = "";
        var music = new Audio("{{asset('/bgm/preparation.mp3')}}");
        $(function(){
            $('#btn').on('click', function() {
                if(play == ""){
                    music.play();
                    $('#speaker').attr('src', "{{asset('/img/speaker_mute.png')}}");
                    play = "play";
                }else{
                    music.pause();
                    $('#speaker').attr('src', "{{asset('/img/speaker.png')}}");
                    play = "";
                }
            });
        });
        </script>
    </head>

    <body>
    <button id="btn" type="button" style="height: 27px; width: 29px;"><img border="0" id="speaker" src="{{asset('/img/speaker.png')}}"></button>
    <form action="{{ url('/login')}}" method="POST">
    {{ csrf_field() }}
    <h1><span>3人麻雀</span></h1>
    <input name="user_name" placeholder="Username" type="text"/>
    <input name="pass" placeholder="Password" type="password"/>
    <button class="btn">Log in</button>
    </div>
    @if ($errors<>"")
        {{$errors}}
    @endif
    </form>
    </body>
</html>