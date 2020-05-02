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
        $(document).ready(function(){
            var ck_flg = "";
            var click_ck = "";
            var new_path = "";
            var select_id = "";
            var path = "";
            var ponkan = "";
            var nakihai_disp = "";
            var update_time = "{{$haipai->update_time}}";
            var player = "{{Session::get('player_no')}}";
            var tumo_player = "{{$haipai->tsumo_ban}}";
            setInterval(function(){
                tumo_name = "#" + tumo_player + "_name";
                $(tumo_name).fadeOut(500,function(){$(this).fadeIn(500)});
            },1000);
            setInterval(function(){
                if(ck_flg != "tumo_ban" && ponkan == ""){
                    $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url: "{{ action('TestController@gamecheck') }}",
                            type: 'POST',
                            data:{'update_time':update_time},
                            dataType:'json'
                        })
                        // Ajaxリクエストが成功した場合
                        .done(function(data) {
                            if (data.result == "OK") {
                                update_time = data.message.update_time;
                                if(data.message.tsumo_ban == player){
                                    $('#tumo_span').show();
                                }else{
                                    $('#tumo_span').hide();
                                }
                                if(data.message.player1_sutehai != ""){
                                    var sutehai_arr = data.message.player1_sutehai.split(',');
                                    var sutehai_disp = "";
                                    if(player == "player1"){
                                        sutehai_disp = "#player_sutehai_";
                                    }
                                    if(player == "player2"){
                                        sutehai_disp = "#kamitya_sutehai_";
                                    }
                                    if(player == "player3"){
                                        sutehai_disp = "#toimen_sutehai_";
                                    }
                                    disp(sutehai_arr,sutehai_disp);
                                }
                                if(data.message.player2_sutehai != ""){
                                    var sutehai_arr = data.message.player2_sutehai.split(',');
                                    var sutehai_disp = "";
                                    if(player == "player1"){
                                        sutehai_disp = "#simotya_sutehai_";
                                    }
                                    if(player == "player2"){
                                        sutehai_disp = "#player_sutehai_";
                                    }
                                    if(player == "player3"){
                                        sutehai_disp = "#kamitya_sutehai_";
                                    }
                                    disp(sutehai_arr,sutehai_disp);
                                }
                                if(data.message.player3_sutehai != ""){
                                    var sutehai_arr = data.message.player3_sutehai.split(',');
                                    var sutehai_disp = "";
                                    if(player == "player1"){
                                        sutehai_disp = "#toimen_sutehai_";
                                    }
                                    if(player == "player2"){
                                        sutehai_disp = "#simotya_sutehai_";
                                    }
                                    if(player == "player3"){
                                        sutehai_disp = "#player_sutehai_";
                                    }
                                    disp(sutehai_arr,sutehai_disp);
                                }
                                $('#nokori_hai').text(data.message.nokori_hai);
                                if(data.message.pon != ""){
                                    $('#pon_span').show();
                                }else{
                                    $('#pon_span').hide();
                                }
                                if(data.message.tsumo_ban.slice(-5) == "_tumo"){
                                    tumo_player = data.message.tsumo_ban.slice(0,-5);
                                }else{
                                    tumo_player = data.message.tsumo_ban;
                                }
                                if(data.message.player1_nakihai != ""){
                                    if(player == "player1" && data.message.player1_nakihai.length != 0){
                                        disp_ji_nakihai(data.message.player1_nakihai.split(','));
                                    }
                                    if(player == "player2"){
                                        nakihai_disp = "#kamitya_nakihai_";
                                    }
                                    if(player == "player3" && data.message.player1_nakihai.length != 0){
                                        disp_toimen_nakihai(data.message.player1_nakihai.split(','),data.message.player1_hai);
                                    }
                                }
                                if(data.message.player2_nakihai != ""){
                                    if(player == "player1"){
                                        nakihai_disp = "#simotya_nakihai_";
                                    }
                                    if(player == "player2" && data.message.player2_nakihai.length != 0){
                                        disp_ji_nakihai(data.message.player2_nakihai.split(','));
                                    }
                                    if(player == "player3"){
                                        nakihai_disp = "#kamitya_nakihai_";
                                    }
                                }
                                if(data.message.player3_nakihai != ""){
                                    if(player == "player1" && data.message.player3_nakihai.length != 0){
                                        disp_toimen_nakihai(data.message.player3_nakihai.split(','),data.message.player3_hai);
                                    }
                                    if(player == "player2"){
                                        nakihai_disp = "#simotya_nakihai_";
                                    }
                                    if(player == "player3" && data.message.player3_nakihai.length != 0){
                                        disp_ji_nakihai(data.message.player3_nakihai.split(','));
                                    }
                                }

//                                    $('#ji_naki').append(hoge);
                            }
                        })
                        // Ajaxリクエストが失敗した場合
                        .fail(function(data) {
                            alert("接続失敗");
                        });
                }
            },5000);
            var tehais = $('[id^=tehai_]').length;
            for (var i = 0; i <= tehais; i++) {
                var select = "#tehai_" + String(i);
                //マウスを乗せたら発動
                $(select).hover(function() {
                    //マウスを乗せたら色が変わる
                    $(this).css('background-color', 'blue');
                    $(this).css('opacity', '0.6');
                //ここにはマウスを離したときの動作を記述
                }, function() {
                    //色指定を空欄にすれば元の色に戻る
                    $(this).css('opacity', '1');
                });
                $(select).on('click', function() {
                    if($('#tehai_tumo').attr('value') != "0h" || ponkan == "ponkan"){
                        if(ck_flg == "tumo_ban" && click_ck == ""){
                            click_ck = "click";
                            var id = "#" + $(this).attr('id');
                            var sutehai = $(id).attr('value');
                            var tumohai = $('#tehai_tumo').attr('value');
                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });
                            $.ajax({
                                url: "{{ action('TestController@sutehai') }}",
                                type: 'POST',
                                data:{'sutehai':sutehai,'tumohai':tumohai,'ponkan':ponkan},
                                dataType:'json'
                            })
                            // Ajaxリクエストが成功した場合
                            .done(function(data) {
                                if (data.result == "OK") {
                                    $('#tehai_tumo').hide();
                                    disp_tehai_sutehai(data);
                                }
                                ck_flg = "";
                                click_ck = "";
                                ponkan = "";
                            })
                            // Ajaxリクエストが失敗した場合
                            .fail(function(data) {
                                alert("接続失敗");
                                click_ck = "";
                            });
                        }
                    }
                });
            }
            function disp_tehai_sutehai(data){
                var hai_disp = "#tehai_";
                if(player == "player1"){
                    var hai_data = data.message.player1_hai;
                    var hai_arr = data.message.player1_hai.split(',');
                }
                if(player == "player2"){
                    var hai_data = data.message.player2_hai;
                    var hai_arr = data.message.player2_hai.split(',');
                }
                if(player == "player3"){
                    var hai_data = data.message.player3_hai;
                    var hai_arr = data.message.player3_hai.split(',');
                }
                disp(hai_arr,hai_disp,tehai = "tehai");

                if(data.message.player1_sutehai != ""){
                    var sutehai_arr = data.message.player1_sutehai.split(',');
                    var sutehai_disp = "";
                    if(player == "player1"){
                        sutehai_disp = "#player_sutehai_";
                        disp(sutehai_arr,sutehai_disp);
                    }
                }
                if(data.message.player2_sutehai != ""){
                    var sutehai_arr = data.message.player2_sutehai.split(',');
                    var sutehai_disp = "";
                    if(player == "player2"){
                        sutehai_disp = "#player_sutehai_";
                        disp(sutehai_arr,sutehai_disp);
                    }
                }
                if(data.message.player3_sutehai != ""){
                    var sutehai_arr = data.message.player3_sutehai.split(',');
                    var sutehai_disp = "";
                    if(player == "player3"){
                        sutehai_disp = "#player_sutehai_";
                        disp(sutehai_arr,sutehai_disp);
                    }
                }
            }
            function disp(hai_arr,hai_disp,tehai = "",pon = ""){
                if(hai_disp == "#simotya_sutehai_" || hai_disp =="#kamitya_sutehai_"){
                    var max = 24;
                }
                if(hai_disp == "#toimen_sutehai_" || hai_disp =="#player_sutehai_"){
                    var max = 40;
                }
                for(var cnt = 0; cnt < max; cnt++){
                    select_id = hai_disp + cnt;
                    path = $(select_id).attr('src').slice(0,-6);
                    new_path = path + "0h.png";
                        $(select_id).attr('src', new_path);
                }
                for(var i=0; i < hai_arr.length; i++){
                    select_id = hai_disp + i;
                    path = $(select_id).attr('src').slice(0,-6);
                    new_path = path + hai_arr[i] + ".png";
                    $(select_id).attr('src', new_path);
                    if(tehai != ""){
                        $(select_id).attr('value', hai_arr[i]);
                    }
                }
                if(pon != ""){
                    i = i- 1;
                    $(select_id).hide();
                    $('#tehai_tumo').attr('src', new_path);
                    $('#tehai_tumo').attr('value', hai_arr[i]);
                    $('#tehai_tumo').show();
                    for(var cnt = i; cnt < 13; cnt++){
                        select_id = hai_disp + cnt;
                        $(select_id).hide();
                    }
                }
            }
            function disp_ji_nakihai(nakihai_arr){
                path = $('#tehai_0').attr('src').slice(0,-6);
                new_path = "";
                var naki_player = "";
                var rotate = "0";
                $("#ji_naki").empty();
                for(var i=0; i < nakihai_arr.length; i++){
                    if(nakihai_arr[i] == "player1"){
                        naki_player = "player1";
                        rotate = "1";
                    }
                    if(nakihai_arr[i] == "player2"){
                        naki_player = "player2";
                        rotate = "2";
                    }
                    if(nakihai_arr[i] == "player3"){
                        naki_player = "player3";
                        rotate = "1";
                    }
                    if(nakihai_arr[i] != "player1" && nakihai_arr[i] != "player2" && nakihai_arr[i] != "player3"){
                        if(naki_player == "player1"){
                            new_path = path + nakihai_arr[i] + ".png";
                            if(rotate == "1"){
                                $('#ji_naki').append('<img src= ' + new_path + '>');
                                rotate = "0";
                            }else{
//                                                        $('#ji_naki').append('<img class="rotate" src= ' + new_path + '>');
                                $('#ji_naki').append('<img src= ' + new_path + '>');
                                rotate = "1";
                            }
                        }
                        if(naki_player == "player2"){
                            new_path = path + nakihai_arr[i] + ".png";
                            if(rotate == "2"){
//                                                        $('#ji_naki').append('<img class="rotate2" src= ' + new_path + '>');
                                $('#ji_naki').append('<img src= ' + new_path + '>');
                                rotate = "0";
                            }else{
                                $('#ji_naki').append('<img src= ' + new_path + '>');
                                rotate = "0";
                            }
                        }
                        if(naki_player == "player3"){
                            new_path = path + nakihai_arr[i] + ".png";
                                $('#ji_naki').append('<img src= ' + new_path + '>');
                        }
                    }
                }
            }
            function disp_toimen_nakihai(nakihai_arr,hai_count){
                path = $('#tehai_0').attr('src').slice(0,-6);
                new_path = "";
                var naki_player = "";
                var rotate = "0";
                $("#toimen_naki").empty();
                for(var i = 0; i < nakihai_arr.length; i++){
                    if(nakihai_arr[i] == "player1"){
                        naki_player = "player1";
                        rotate = "1";
                    }
                    if(nakihai_arr[i] == "player2"){
                        naki_player = "player2";
                        rotate = "2";
                    }
                    if(nakihai_arr[i] == "player3"){
                        naki_player = "player3";
                        rotate = "3";
                    }
                    if(nakihai_arr[i] != "player1" && nakihai_arr[i] != "player2" && nakihai_arr[i] != "player3"){
                        if(naki_player == "player1"){
                            new_path = path + nakihai_arr[i] + ".png";
                            if(rotate == "1"){
                                $('#toimen_naki').append('<img class="rotate1" src= ' + new_path + '>');
                                rotate = "0";
                            }else{
//                                                        $('#ji_naki').append('<img class="rotate" src= ' + new_path + '>');
                                $('#toimen_naki').append('<img  class="rotate1" src= ' + new_path + '>');
                                rotate = "1";
                            }
                        }
                        if(naki_player == "player2"){
                            new_path = path + nakihai_arr[i] + ".png";
                            if(rotate == "2"){
//                                                        $('#ji_naki').append('<img class="rotate2" src= ' + new_path + '>');
                                $('#toimen_naki').append('<img class="rotate1" src= ' + new_path + '>');
                                rotate = "0";
                            }else{
                                $('#toimen_naki').append('<img class="rotate1" src= ' + new_path + '>');
                                rotate = "0";
                            }
                        }
                        if(naki_player == "player3"){
                            new_path = path + nakihai_arr[i] + ".png";
                            if(rotate == "3"){
                                $('#toimen_naki').append('<img class="rotate1" src= ' + new_path + '>');
                                rotate = "0";
                            }else{
                                $('#toimen_naki').append('<img class="rotate1" src= ' + new_path + '>');
                                rotate = "0";
                            }
                        }
                    }
                }
                var hai = 13 - hai_count;
                for(var i=0; i < hai; i++){
                    select_id = "#toimen_tehai_" + String(i + hai_count);
                    $(select_id).hide();
                }
            }
            $('#tehai_tumo').on('click', function() {
                if(ck_flg == "tumo_ban" && click_ck == ""){
                    click_ck = "click";
                    var id = $(this).attr('id');
                    var sutehai = $('#tehai_tumo').attr('value');
                    var tumohai = "";
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ action('TestController@sutehai') }}",
                        type: 'POST',
                        data:{'sutehai':sutehai,'tumohai':tumohai,'ponkan':ponkan},
                        dataType:'json'
                    })
                    // Ajaxリクエストが成功した場合
                    .done(function(data) {
                        if (data.result == "OK") {
                            $('#tumo_span').hide();
                            $('#tehai_tumo').hide();
                            disp_tehai_sutehai(data);
                            ck_flg = "";
                        }
                        click_ck = "";
                        ponkan = "";
                    })
                    // Ajaxリクエストが失敗した場合
                    .fail(function(data) {
                        alert("接続失敗");
                        click_ck = "";
                    });
                }
            });
            $('#tumo').on('click', function() {
                if(click_ck == ""){
                    $('#pon_span').hide();
                    click_ck = "click";
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ action('TestController@tumo') }}",
                        type: 'POST',
                        dataType:'json'
                    })
                    // Ajaxリクエストが成功した場合
                    .done(function(data) {
                        if (data.result == "OK") {
                            path = $('#tehai_tumo').attr('src').slice(0,-6);
                            new_path = path + data.message + ".png";
                            $('#tehai_tumo').attr('src', new_path);
                            $('#tehai_tumo').attr('value', data.message);
                            $('#tehai_tumo').show();
                            $('#tehai_tumo').hover(function() {
                                //マウスを乗せたら色が変わる
                                $(this).css('background-color', 'blue');
                                $(this).css('opacity', '0.6');
                                //ここにはマウスを離したときの動作を記述
                                }, function() {
                                    //色指定を空欄にすれば元の色に戻る
                                    $(this).css('opacity', '1');
                                });
                            }
                            $('#tumo_span').hide();
                            ck_flg = "tumo_ban";
                            click_ck = "";
                            
                    })
                    // Ajaxリクエストが失敗した場合
                    .fail(function(data) {
                        alert("接続失敗");
                        click_ck = "";
                    });
                }
            });
            $('#pon').on('click', function() {
                if(click_ck == ""){
                    $('#tumo_span').hide();
                    click_ck = "click";
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ action('TestController@pon') }}",
                        type: 'POST',
                        dataType:'json'
                    })
                    // Ajaxリクエストが成功した場合
                    .done(function(data) {
                        if (data.result == "OK") {
                            var hai_disp = "#tehai_";
                            if(player == "player1"){
                                var hai_arr = data.message.player1_hai.split(',');
                            }
                            if(player == "player2"){
                                var hai_arr = data.message.player2_hai.split(',');
                            }
                            if(player == "player3"){
                                var hai_arr = data.message.player3_hai.split(',');
                            }
                            disp(hai_arr,hai_disp,tehai = "tehai",pon = "pon");
                            if(data.message.player1_sutehai != ""){
                                var sutehai_data = data.message.player1_sutehai;
                                var sutehai_arr = sutehai_data.split(',');
                                var sutehai_disp = "";
                                if(player == "player1"){
                                    sutehai_disp = "#player_sutehai_";
                                }
                                if(player == "player2"){
                                    sutehai_disp = "#kamitya_sutehai_";
                                }
                                if(player == "player3"){
                                    sutehai_disp = "#toimen_sutehai_";
                                }
                                disp(sutehai_arr,sutehai_disp);
                            }
                            if(data.message.player2_sutehai != ""){
                                var sutehai_data = data.message.player2_sutehai;
                                var sutehai_arr = sutehai_data.split(',');
                                var sutehai_disp = "";
                                if(player == "player1"){
                                    sutehai_disp = "#simotya_sutehai_";
                                }
                                if(player == "player2"){
                                    sutehai_disp = "#player_sutehai_";
                                }
                                if(player == "player3"){
                                    sutehai_disp = "#kamitya_sutehai_";
                                }
                                disp(sutehai_arr,sutehai_disp);
                            }
                            if(data.message.player3_sutehai != ""){
                                var sutehai_data = data.message.player3_sutehai;
                                var sutehai_arr = sutehai_data.split(',');
                                var sutehai_disp = "";
                                if(player == "player1"){
                                    sutehai_disp = "#toimen_sutehai_";
                                }
                                if(player == "player2"){
                                    sutehai_disp = "#simotya_sutehai_";
                                }
                                if(player == "player3"){
                                    sutehai_disp = "#player_sutehai_";
                                }
                                disp(sutehai_arr,sutehai_disp);
                            }
                            $('#nokori_hai').text(data.message.nokori_hai);
                            if(data.message.pon != ""){
                                $('#pon_span').show();
                            }else{
                                $('#pon_span').hide();
                            }
                            if(data.message.tsumo_ban.slice(-5) == "_tumo"){
                                tumo_player = data.message.tsumo_ban.slice(0,-5);
                            }else{
                                tumo_player = data.message.tsumo_ban;
                            }
                            if(data.message.player1_nakihai != ""){
                                if(player == "player1"){
                                    disp_ji_nakihai(data.message.player1_nakihai.split(','));
                                }
                                if(player == "player2"){
                                    nakihai_disp = "#kamitya_nakihai_";
                                }
                                if(player == "player3"){
                                    nakihai_disp = "#toimen_nakihai_";
                                }
                            }
                            if(data.message.player2_nakihai != ""){
                                if(player == "player1"){
                                    nakihai_disp = "#simotya_nakihai_";
                                }
                                if(player == "player2"){
                                    disp_ji_nakihai(data.message.player2_nakihai.split(','));
                                }
                                if(player == "player3"){
                                    nakihai_disp = "#kamitya_nakihai_";
                                }
                            }
                            if(data.message.player3_nakihai != ""){
                                if(player == "player1"){
                                    nakihai_disp = "#toimen_nakihai_";
                                }
                                if(player == "player2"){
                                    nakihai_disp = "#simotya_nakihai_";
                                }
                                if(player == "player3"){
                                    disp_ji_nakihai(data.message.player3_nakihai.split(','));
                                }
                            }
                            ck_flg = "tumo_ban";
                            ponkan = "ponkan";
                            click_ck = "";
                            $('#pon_span').hide();
                            $('#tumo_span').hide();
                        }
                    })
                    // Ajaxリクエストが失敗した場合
                    .fail(function(data) {
                        alert("接続失敗");
                        click_ck = "";
                    });
                }
            });
        });
        </script>
    </head>
    <body>
        <header class="header">
        {{-- 対面 --}}
        @if(Session::get('player_no') == "player1")
            @if($game_status->oya_ban == "player3")
                親
            @endif
            <span id="player3_name">{{$game_status->user3}}</span> 持ち点 {{$game_status->player3_ten}}<br>
        @endif
        @if(Session::get('player_no') == "player3")
            @if($game_status->oya_ban == "player1")
                親
            @endif
        <span id="player1_name">{{$game_status->user1}}</span> 持ち点 {{$game_status->player1_ten}}<br>
        @endif
        @if(Session::get('player_no') =="player1" || Session::get('player_no') =="player3")
            <span id="toimen_naki"></span>
            
            <?php
                //対面手牌
                if(Session::get('player_no') =="player1"){
                    $tehai = explode(',',$haipai->player3_hai);
                }
                if(Session::get('player_no') =="player3"){
                    $tehai = explode(',',$haipai->player1_hai);
                }
                if(Session::get('player_no') !="player2"){
                    $cnt = 0;
                    foreach($tehai as $val){
                        $id = "id=\"toimen_tehai_" . $cnt . "\""; 
                        $img_path= asset("/img/hai/yoko.png");
                        echo "<img " . $id . "class=\"rotate1\" src= " . $img_path . ">";
                        $cnt++;
                    }
                }
            ?><br>
        @endif
        </header>
        <main class="main-wrap">
        <aside class="side">
        {{-- 上家 --}}
            @if(Session::get('player_no') =="player2")
                @if($game_status->oya_ban == "player1")
                    親
                @endif
                <span id="player1_name">{{$game_status->user1}}</span><br>
                 持ち点 {{$game_status->player1_ten}}
            @endif
            @if(Session::get('player_no') =="player3")
                @if($game_status->oya_ban == "player2")
                    親
                @endif
                <span id="player2_name">{{$game_status->user2}}</span><br>
                 持ち点 {{$game_status->player2_ten}}
            @endif
            <br><br>
            @if(Session::get('player_no') =="player2" || Session::get('player_no') =="player3")
                <?php
                    //上家手牌
                    if(Session::get('player_no') =="player2"){
                        $tehai = explode(',',$haipai->player1_hai);
                        foreach($tehai as $val){
                            $img_path= asset("/img/hai/yoko.png");
                            echo "<img class=\"rotate\" src= " . $img_path . "><br>";
                        }
                    }
                    if(Session::get('player_no') =="player3"){
                        $tehai = explode(',',$haipai->player2_hai);
                        foreach($tehai as $val){
                            $img_path= asset("/img/hai/yoko.png");
                            echo "<img class=\"rotate\" src= " . $img_path . "><br>";
                        }
                    }
                ?>
                <span id="kamitya_naki"></span>
                <?php
                    //上家鳴き牌
                    if(Session::get('player_no') =="player2"){
                        $nakihai = explode(',',$haipai->player1_nakihai);
                        foreach($nakihai as $val){
                            if($val != null){
                                $img_path= asset("/img/hai/" . $val . ".png");
                                echo "<img class=\"rotate\" src= " . $img_path . "><br>";
                            }
                        }
                    }
                    if(Session::get('player_no') =="player3"){
                        $nakihai = explode(',',$haipai->player2_nakihai);
                        foreach($nakihai as $val){
                            if($val != null){
                                $img_path= asset("/img/hai/" . $val . ".png");
                                echo "<img class=\"rotate\" src= " . $img_path . "><br>";
                            }
                        }
                    }
                ?><br>
            @endif
        </aside>
        <article class="center">
        {{-- 対面捨て配 --}}
            <article class="c_head">
                <div style="text-align:-webkit-right;">
                    <table cellspacing="0">
                    <?php
                        //対面
                        $img_path= asset("/img/hai/0h.png");
                        echo "<tr>";
                        for($i = 0;$i < 20;$i++){
                            $cnt = 39 - $i;
                            $id = "id=\"toimen_sutehai_" . $cnt . "\""; 
                            echo "<td><img " . $id . "class=\"rotate1\" src= " . $img_path . "></td>";
                        }
                        echo "</tr>";
                        echo "<tr>";
                        for($i = 0;$i < 20;$i++){
                            $cnt = 19 - $i;
                            $id = "id=\"toimen_sutehai_" . $cnt . "\""; 
                            echo "<td><img " . $id . "class=\"rotate1\" src= " . $img_path . "></td>";
                        }
                        echo "</tr>";
                    ?>
                    </table>
                </div>
            </article>
            {{-- 上家捨て配 --}}
            <article class="c_side">
                <div>
                    <table cellspacing="0">
                    <?php
                        // 捨て配テーブル
                        // 24 18 12 6  0
                        // 25 19 13 7  1
                        // 26 20 14 8  2
                        // 27 21 15 9  3
                        // 28 22 16 10 4
                        // 29 23 17 11 5
                        $img_path= asset("/img/hai/0h.png");
                        for($i = 0;$i < 6;$i++){
                            $id1 = "id=\"kamitya_sutehai_" . $i . "\"";
                            $id2 = "id=\"kamitya_sutehai_" . ($i + 6) . "\"";
                            $id3 = "id=\"kamitya_sutehai_" . ($i + 12) . "\"";
                            $id4 = "id=\"kamitya_sutehai_" . ($i + 18) . "\"";
                            echo "<tr><td class=\"td2\"><img " . $id4 . "class=\"rotate\" src= " . $img_path . "></td><td class=\"td2\"><img " . $id3 . "class=\"rotate\" src= " . $img_path . "></td><td class=\"td2\"><img " . $id2 . "class=\"rotate\" src= " . $img_path . "></td><td class=\"td2\"><img " . $id1 . "class=\"rotate\" src= " . $img_path . "></td></tr>";
                        }
                    ?>
                    </table>
                </div>
            </article>
            <article class="c_sicro1">
            <?php
                $kyoku = explode(',',$game_status->kyoku);
                if($kyoku[0] == "t"){
                    $ba = "東場";
                }else{
                    $ba = "南場";
                }
                $nokori = explode(',',$haipai->nokori_hai);
                $kan1 = 0;
                $kan2 = 0;
                $kan3 = 0;
                if($haipai->player1_ponkan != ""){
                    $ponkan = explode(',',$haipai->player1_ponkan);
                    $kan1 = $ponkan[1];
                }
                if($haipai->player2_ponkan != ""){
                    $ponkan = explode(',',$haipai->player2_ponkan);
                    $kan2 = $ponkan[1];
                }
                if($haipai->player3_ponkan != ""){
                    $ponkan = explode(',',$haipai->player3_ponkan);
                    $kan3 = $ponkan[1];
                }
                $dorayama_hai = explode(',',$haipai->dorayama_hai);
                if(($kan1 + $kan2 + $kan3) == 0){
                    $img_path= asset("/img/hai/ura.png");
                    $dora_path = asset("/img/hai/" . $dorayama_hai[4] . ".png");
                    $dorayama = "<td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $dora_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td>";
                }
                if(($kan1 + $kan2 + $kan3) == 1){
                    $img_path= asset("/img/hai/ura.png");
                    $dora_path = asset("/img/hai/" . $dorayama_hai[4] . ".png");
                    $dora_path1 = asset("/img/hai/" . $dorayama_hai[6] . ".png");
                    $dorayama = "<td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $dora_path . "></td><td><img src= " . $dora_path1 . "></td><td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td>";
                }
                if(($kan1 + $kan2 + $kan3) == 2){
                    $img_path= asset("/img/hai/ura.png");
                    $dora_path = asset("/img/hai/" . $dorayama_hai[4] . ".png");
                    $dora_path1 = asset("/img/hai/" . $dorayama_hai[6] . ".png");
                    $dora_path2 = asset("/img/hai/" . $dorayama_hai[8] . ".png");
                    $dorayama = "<td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $dora_path . "></td><td><img src= " . $dora_path1 . "></td><td><img src= " . $dora_path2 . "></td><td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td>";
                }
                if(($kan1 + $kan2 + $kan3) == 3){
                    $img_path= asset("/img/hai/ura.png");
                    $dora_path = asset("/img/hai/" . $dorayama_hai[4] . ".png");
                    $dora_path1 = asset("/img/hai/" . $dorayama_hai[6] . ".png");
                    $dora_path2 = asset("/img/hai/" . $dorayama_hai[8] . ".png");
                    $dora_path3 = asset("/img/hai/" . $dorayama_hai[10] . ".png");
                    $dorayama = "<td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $dora_path . "></td><td><img src= " . $dora_path1 . "></td><td><img src= " . $dora_path2 . "></td><td><img src= " . $dora_path3 . "></td><td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td>";
                }
                if(($kan1 + $kan2 + $kan3) == 4){
                    $img_path= asset("/img/hai/ura.png");
                    $dora_path = asset("/img/hai/" . $dorayama_hai[4] . ".png");
                    $dora_path1 = asset("/img/hai/" . $dorayama_hai[6] . ".png");
                    $dora_path2 = asset("/img/hai/" . $dorayama_hai[8] . ".png");
                    $dora_path3 = asset("/img/hai/" . $dorayama_hai[10] . ".png");
                    $dora_path4 = asset("/img/hai/" . $dorayama_hai[12] . ".png");
                    $dorayama = "<td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $dora_path . "></td><td><img src= " . $dora_path1 . "></td><td><img src= " . $dora_path2 . "></td><td><img src= " . $dora_path3 . "></td><td><img src= " . $dora_path4 . "></td><td><img src= " . $img_path . "></td>";
                }
                if(($kan1 + $kan2 + $kan3) == 5){
                    $img_path= asset("/img/hai/ura.png");
                    $dora_path = asset("/img/hai/" . $dorayama_hai[4] . ".png");
                    $dora_path1 = asset("/img/hai/" . $dorayama_hai[6] . ".png");
                    $dora_path2 = asset("/img/hai/" . $dorayama_hai[8] . ".png");
                    $dora_path3 = asset("/img/hai/" . $dorayama_hai[10] . ".png");
                    $dora_path4 = asset("/img/hai/" . $dorayama_hai[12] . ".png");
                    $dora_path5 = asset("/img/hai/" . $dorayama_hai[14] . ".png");
                    $dorayama = "<td><img src= " . $img_path . "></td><td><img src= " . $img_path . "></td><td><img src= " . $dora_path . "></td><td><img src= " . $dora_path1 . "></td><td><img src= " . $dora_path2 . "></td><td><img src= " . $dora_path3 . "></td><td><img src= " . $dora_path4 . "></td><td><img src= " . $dora_path5 . "></td>";
                }
            ?>
            <font color="#ff9999"><span id ="ba">{{$ba}}</span>&nbsp;<span id ="kyoku">{{$kyoku[1]}}</span>局&nbsp;&nbsp;<span id ="honba">{{$kyoku[2]}}</span>本場</font><br>
            <font color="#ff9999">残り牌<span id ="nokori_hai">{{count($nokori)}}</span></font><br>
            <?php echo $dorayama; ?> 
            </article>
            {{-- 下家捨て配 --}}
            <article class="c_side">
                <div>
                    <table cellspacing="0">
                    <?php
                        // 捨て配テーブル
                        // 5 11 17 23 29
                        // 4 10 16 22 28
                        // 3  9 15 21 27
                        // 2  8 14 20 26
                        // 1  7 13 19 25
                        // 0  6 12 18 24
                        $img_path= asset("/img/hai/0h.png");
                        for($i = 0;$i < 6;$i++){
                            $id4 = "id=\"simotya_sutehai_" . (5 - $i) . "\"";
                            $id3 = "id=\"simotya_sutehai_" . (11- $i) . "\"";
                            $id2 = "id=\"simotya_sutehai_" . (17 -$i) . "\"";
                            $id1 = "id=\"simotya_sutehai_" . (23 -$i) . "\"";
                            echo "<tr><td class=\"td2\"><img " . $id4 . "class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img " . $id3 . "class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img " . $id2 . "class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img " . $id1 . "class=\"rotate2\" src= " . $img_path . "></td></tr>";
                        }
                    ?>
                    </table>
                </div>
            </article>
            {{-- プレイヤー捨て配 --}}
            <article class="c_footer">
                <div>
                    <table cellspacing="0">
                    <?php
                        $img_path= asset("/img/hai/0h.png");
                        echo "<tr>";
                        for($i = 0;$i < 20;$i++){
                            $id = "id=\"player_sutehai_" . $i . "\""; 
                            echo "<td><img " . $id . "src= " . $img_path . "></td>";
                        }
                        echo "</tr>";
                        echo "<tr>";
                        for($i = 0;$i < 20;$i++){
                            $cnt = $i + 20;
                            $id = "id=\"player_sutehai_" . $cnt . "\""; 
                            echo "<td><img " . $id . "src= " . $img_path . "></td>";
                        }
                        echo "</tr>";
                    ?>
                    </table>
                </div>
            </article>
        </article>
        {{-- 下家 --}}
        <aside class="side">
            @if(Session::get('player_no') =="player1" || Session::get('player_no') =="player2")
                @if(Session::get('player_no') =="player1")
                    @if($game_status->oya_ban == "player2")
                        親
                    @endif
                    <span id="player2_name">{{$game_status->user2}}</span><br>
                    持ち点 {{$game_status->player2_ten}}<br><br>
                    <?php
                        //下家鳴き牌
                        $nakihai = explode(',',$haipai->player2_nakihai);
                        foreach($nakihai as $val){
                            if($val != null){
                                $img_path= asset("/img/hai/" . $val . ".png");
                                echo "<img class=\"rotate2\" src= " . $img_path . "><br>";
                            }
                        }
                    ?>
                    <?php
                        //下家手牌
                        $tehai = explode(',',$haipai->player2_hai);
                        foreach($tehai as $val){
                            $img_path= asset("/img/hai/yoko.png");
                            echo "<img class=\"rotate2\" src= " . $img_path . "><br>";
                        }
                    ?><br>
                @endif
                @if(Session::get('player_no') =="player2")
                    @if($game_status->oya_ban == "player3")
                        親
                    @endif
                    <span id="player3_name">{{$game_status->user3}}</span><br>
                    持ち点 {{$game_status->player3_ten}}<br><br>
                    <?php
                        //下家鳴き牌
                        $nakihai = explode(',',$haipai->player1_nakihai);
                        foreach($nakihai as $val){
                            if($val != null){
                                $img_path= asset("/img/hai/" . $val . ".png");
                                echo "<img class=\"rotate2\" src= " . $img_path . "><br>";
                            }
                        }
                    ?>
                    <?php
                        //下家手牌
                        $tehai = explode(',',$haipai->player1_hai);
                        foreach($tehai as $val){
                            $img_path= asset("/img/hai/yoko.png");
                            echo "<img class=\"rotate2\" src= " . $img_path . "><br>";
                        }
                    ?><br>
                @endif
            @endif
        </aside>
        </main>
        <footer class="footer">
        @if(Session::get('player_no') =="player1" && $game_status->oya_ban == "player1")
            親
        @endif
        @if(Session::get('player_no') =="player2" && $game_status->oya_ban == "player2")
            親
        @endif
        @if(Session::get('player_no') =="player3" && $game_status->oya_ban == "player3")
            親
        @endif
        {{Session::get('user')}}
        @if(Session::get('player_no') =="player1")
            持ち点 {{$game_status->player1_ten}}
        @endif
        @if(Session::get('player_no') =="player2")
            持ち点 {{$game_status->player2_ten}}
        @endif
        @if(Session::get('player_no') =="player3")
            持ち点 {{$game_status->player3_ten}}
        @endif <br>
        <?php
        //手牌
            if(Session::get('player_no') =="player1"){
                $tehai = explode(',',$haipai->player1_hai);
            }
            if(Session::get('player_no') =="player2"){
                $tehai = explode(',',$haipai->player2_hai);
            }
            if(Session::get('player_no') =="player3"){
                $tehai = explode(',',$haipai->player3_hai);
            }
            $cnt = 0;
            foreach($tehai as $val){
                $id = "id=\"tehai_" . $cnt . "\""; 
                $img_path= asset("/img/hai/" . $val . ".png");
                echo "<img " . $id . "value= \"". $val . "\" src= " . $img_path . ">";
                $cnt++;
            }
            // ツモ牌
            $img_path= asset("/img/hai/0h.png");
            echo "<span>&nbsp;&nbsp;</span><img style=\"display:none;\" id=\"tehai_tumo\" value= \"0h\" src= " . $img_path . ">";
            echo "<span>&nbsp;&nbsp;</span>";
        ?>
        <span id="ji_naki"></span>
        <br><br>

        <span id="tumo_span" style="display:none;"><button id="tumo" type="button">ツモ</button></span>
        <span id="lon_span" style="display:none;"><button id="lon" type="button">ロン</button></span>
        <span id="pon_span" style="display:none;"><button id="pon" type="button">ポン</button></span>
        <span id="kan_span" style="display:none;"><button id="kan" type="button">カン</button></span>
        <span id="tumoagari_span" style="display:none;"><button id="tumoagari"" type="button">ツモ上がり</button></span>
        </footer>
    </body>
</html>
