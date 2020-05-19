<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>majan</title>
        <link rel="stylesheet" href="{{ asset('/css/test.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/scss.css') }}">
        <link rel="stylesheet" href="{{ asset('js/jquery-ui-1.12.1/jquery-ui.css') }}">
        <script src="{{asset('/js/jquery-3.5.0.min.js')}}"></script>
        <script src="{{ asset('js/jquery-ui-1.12.1/jquery-ui.min.js') }}"></script>
        <script>
        $(document).ready(function(){
            var ck_flg = "";
            var click_ck = "";
            var new_path = "";
            var select_id = "";
            var path = "";
            var ponkan = "";
            var reach = "";
            var reach_flg = "";
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
                                update_disp(data);
                            }
                        })
                        // Ajaxリクエストが失敗した場合
                        .fail(function(data) {
                            alert("接続失敗");
                        });
                }
            },2000);
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
                        if(ck_flg == "tumo_ban" && click_ck == "" && reach_flg == ""){
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
                                data:{'sutehai':sutehai,'tumohai':tumohai,'ponkan':ponkan,'reach':reach},
                                dataType:'json'
                            })
                            // Ajaxリクエストが成功した場合
                            .done(function(data) {
                                if (data.result == "OK") {
                                    $('#tehai_tumo').hide();
                                    $('#reach_span').hide();
                                    disp_tehai_sutehai(data);
                                    $('#tumoagari_span').hide();
                                    $('#lon_span').show();
                                }
                                ck_flg = "";
                                click_ck = "";
                                ponkan = "";
                                reach = "";
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
            function update_disp(data){
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
                    if(player == "player1" && data.message.player1_nakihai.length != 0){
                        disp_nakihai(data.message.player1_nakihai.split(','),0,"ji");
                    }
                    if(player == "player2"){
                        disp_nakihai(data.message.player1_nakihai.split(','),data.message.player1_hai,"kamitya");
                    }
                    if(player == "player3" && data.message.player1_nakihai.length != 0){
                        disp_nakihai(data.message.player1_nakihai.split(','),data.message.player1_hai,"toimen");
                    }
                }
                if(data.message.player2_nakihai != ""){
                    if(player == "player1"){
                        disp_nakihai(data.message.player2_nakihai.split(','),data.message.player2_hai,"simotya");
                    }
                    if(player == "player2" && data.message.player2_nakihai.length != 0){
                        disp_nakihai(data.message.player2_nakihai.split(','),0,"ji");
                    }
                    if(player == "player3"){
                        disp_nakihai(data.message.player2_nakihai.split(','),data.message.player2_hai,"kamitya");
                    }
                }
                if(data.message.player3_nakihai != ""){
                    if(player == "player1" && data.message.player3_nakihai.length != 0){
                        disp_nakihai(data.message.player3_nakihai.split(','),data.message.player3_hai,"toimen");
                    }
                    if(player == "player2"){
                        disp_nakihai(data.message.player3_nakihai.split(','),data.message.player3_hai,"simotya");
                    }
                    if(player == "player3" && data.message.player3_nakihai.length != 0){
                        disp_nakihai(data.message.player3_nakihai.split(','),0,"ji");
                    }
                }
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
                    var hai_value = "";
                    if(hai_arr[i].length == 3){
                        hai_value = hai_arr[i].substr(1,2);
                    }else{
                        hai_value = hai_arr[i];
                    }
                    new_path = path + hai_value + ".png";
                    $(select_id).attr('src', new_path);
                    if(tehai != ""){
                        $(select_id).attr('value', hai_value);
                    }
                    if(hai_arr[i].length == 3){
                        if(hai_disp =="#kamitya_sutehai_"){
                            $(select_id).addClass("rotate1");
                            $('#reach_kamitya_span').show();
                        }
                        if(hai_disp == "#simotya_sutehai_"){
                            $(select_id).removeClass("rotate2");
                            $('#reach_simotya_span').show();
                        }
                        if(hai_disp == "#toimen_sutehai_"){
                            $(select_id).addClass("rotate2");
                            $('#reach_toimen_span').show();
                        }
                        if(hai_disp =="#player_sutehai_"){
                            $(select_id).addClass("rotate");
                        }
                    }
                }
                if(pon != ""){
                    i = i- 1;
                    $(select_id).hide();
                    $('#tehai_tumo').attr('src', new_path);
                    $('#tehai_tumo').attr('value', hai_value);
                    $('#tehai_tumo').show();
                    for(var cnt = i; cnt < 13; cnt++){
                        select_id = hai_disp + cnt;
                        $(select_id).hide();
                    }
                }
            }
            function disp_nakihai(nakihai_arr,hai_count = 0,position = ""){
                path = $('#tehai_0').attr('src').slice(0,-6);
                new_path = "";
                select_id = "#" + position + "_naki";
                $(select_id).empty();
                var img = '';
                for(var i=0; i < nakihai_arr.length; i++){
                    new_path = path + nakihai_arr[i] + ".png";
                    if(position == "ji"){
                        img = '<img src= ' + new_path + '>';
                    }
                    if(position == "toimen"){
                        img = '<img class="rotate1" src= ' + new_path + '>';
                    }
                    if(position == "kamitya"){
                        img = '<img class="rotate" src= ' + new_path + '><br><br><br><br>';
                    }
                    if(position == "simotya"){
                        img = '<img class="rotate2" src= ' + new_path + '><br>';
                        if( i > 1){
                            img = img + '<br><br><br>';
                        }
                    }
                    $(select_id).append(img);
                }
                if(position != "ji"){
                    var cnt = 0;
                    for(var i=0; i < hai_count;i++){
                        select_id = "#" + position + "_tehai_" +String(i);
                        $(select_id).show();
                    }
                    var hai = 13 - hai_count;
                    for(var i=0; i < hai; i++){
                        select_id = "#" + position + "_tehai_" + String(i + hai_count);
                        $(select_id).hide();
                    }
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
                        data:{'sutehai':sutehai,'tumohai':tumohai,'ponkan':ponkan,'reach':reach},
                        dataType:'json'
                    })
                    // Ajaxリクエストが成功した場合
                    .done(function(data) {
                        if (data.result == "OK") {
                            $('#tumo_span').hide();
                            $('#tehai_tumo').hide();
                            $('#reach_span').hide();
                            disp_tehai_sutehai(data);
                            $('#tumoagari_span').hide();
                            $('#lon_span').show();
                            ck_flg = "";
                            reach = "";
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
                            if(data.tenpai == "tenpai"){
                                if(data.reach ==""){
                                    $('#reach_span').show();
                                }else{
                                    reach_flg ="reach";
                                }
                            }
                            $('#tumoagari_span').show();
                            $('#lon_span').hide();
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

                            update_disp(data);
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
            $('#reach').on('click', function(){
                if(click_ck == ""){
                    $('#reach_sengen_span').show();
                    click_ck = "click";
                    setTimeout(function(){
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url: "{{ action('TestController@reach') }}",
                            type: 'POST',
                            dataType:'json'
                        })
                        // Ajaxリクエストが成功した場合
                        .done(function(data) {
                            if (data.result == "OK") {
                                reach = "reach";
                                $('#reach_span').hide();
                            }
                            click_ck = "";
                        })
                        // Ajaxリクエストが失敗した場合
                        .fail(function(data) {
                            alert("接続失敗");
                            click_ck = "";
                        });
                    },2000);
                }
            });
            $('#tumoagari').on('click', function() {
                if(click_ck == ""){
                    click_ck = "click";
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ action('TestController@tumoagari') }}",
                        type: 'POST',
                        dataType:'json'
                    })
                    // Ajaxリクエストが成功した場合
                    .done(function(data) {
                        if (data.result == "OK") {
                            for(key in data.message){
                                $('#yaku_1').text(key);
                                $('#han_1').text(data.message[key]);
                            }
                            $('.js-modal').fadeIn();
                        }
                        click_ck = "";
                    })
                    // Ajaxリクエストが失敗した場合
                    .fail(function(data) {
                        alert("接続失敗");
                        click_ck = "";
                    });
                }
            });
            $('.js-modal-open').on('click',function(){
                $('.js-modal').fadeIn();
                return false;
            });
            $('.js-modal-close').on('click',function(){
                $('.js-modal').fadeOut();
                return false;
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
            {{-- 対面鳴き牌 --}}
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
                    }
                    if(Session::get('player_no') =="player3"){
                        $tehai = explode(',',$haipai->player2_hai);
                    }
                    if(Session::get('player_no') !="player1"){
                        $cnt = 0;
                        $img = "";
                        foreach($tehai as $val){
                            $id = "id=\"kamitya_tehai_" . $cnt . "\""; 
                            $img_path= asset("/img/hai/yoko.png");
                            $img .= "<img " . $id . "class=\"rotate\" src= " . $img_path . "></br>";
                            $cnt++;
                        }
                        echo $img;
                    }
                ?>
                
                {{-- 上家鳴き牌 --}}
                <span id="kamitya_naki"></span><br>
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
            <div class="sicro_in1">
                <font color="#ff9999"><span id ="ba">{{$ba}}</span>&nbsp;<span id ="kyoku">{{$kyoku[1]}}</span>局&nbsp;&nbsp;<span id ="honba">{{$kyoku[2]}}</span>本場</font><br>
                <font color="#ff9999">残り牌<span id ="nokori_hai">{{count($nokori)}}</span></font><br>
                <?php echo $dorayama; ?>
            </div>
            <div class="sicro_in2">
            <?php
                $img_path= asset("/img/reach.png");
                echo "<span id=\"reach_toimen_span\" style=\"display:none;\"><div id=\"reach_toimen\"><img class=\"rotate2\" style=\"margin:-30px;\" src=" . $img_path . " ></div></span>";            ?>
            </div>
            <div class="sicro_in3">
            <?php
                $img_path= asset("/img/reach.png");
                echo "<br><span id=\"reach_kamitya_span\" style=\"display:none;\"><div id=\"reach_simotya\"><img src=" . $img_path . " ></div></span>";
            ?>
            </div>
            <div class="sicro_in4"></div>
            <div class="sicro_in5">
            <?php
                $img_path= asset("/img/reach.png");
                echo "<br><span id=\"reach_simotya_span\" style=\"display:none;\"><div id=\"reach_simotya\"><img src=" . $img_path . " ></div></span>";
            ?>
            </div>
            <div class="sicro_in2">
            <?php
                $img_path= asset("/img/reach.png");
                echo "<span id=\"reach_sengen_span\" style=\"display:none;\"><div id=\"reach_sengen\"><img class=\"rotate2\" style=\"margin:-30px;\" src=" . $img_path . " ></div></span>";
            ?>
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
                    <?php
                        $img_path= asset("/img/hai/0h.png");
                        for($i = 0;$i < 20;$i++){
                            $id = "id=\"player_sutehai_" . $i . "\""; 
                            echo "<img " . $id . "src= " . $img_path . ">";
                        }
                        for($i = 0;$i < 20;$i++){
                            $cnt = $i + 20;
                            $id = "id=\"player_sutehai_" . $cnt . "\""; 
                            echo "<img " . $id . "src= " . $img_path . ">";
                        }
                    ?>
                </div>
            </article>
        </article>
        {{-- 下家 --}}
        <aside class="side">
            @if(Session::get('player_no') =="player1" || Session::get('player_no') =="player2")
                @if($game_status->oya_ban == "player2")
                    親
                @endif
                @if($game_status->oya_ban == "player3")
                    親
                @endif
                @if(Session::get('player_no') =="player1")
                    <span id="player2_name">{{$game_status->user2}}</span><br>
                    持ち点 {{$game_status->player2_ten}}<br><br><br>
                @else
                    <span id="player3_name">{{$game_status->user3}}</span><br>
                    持ち点 {{$game_status->player3_ten}}<br><br><br>
                @endif
                {{-- 下家鳴き牌 --}}
                <span id="simotya_naki"></span><br><br><br><br>
                <?php
                //下家手牌
                if(Session::get('player_no') =="player1"){
                    $tehai = explode(',',$haipai->player2_hai);
                }
                if(Session::get('player_no') =="player2"){
                    $tehai = explode(',',$haipai->player3_hai);
                }
                if(Session::get('player_no') !="player3"){
                    $cnt = 0;
                    foreach($tehai as $val){
                        $id = "id=\"simotya_tehai_" . $cnt . "\""; 
                        $img_path= asset("/img/hai/yoko.png");
                        echo "<img " . $id . "class=\"rotate2\" src= " . $img_path . "></br>";
                        $cnt++;
                    }
                }
                ?><br>
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
        <span id="reach_span" style="display:none;"><button id="reach" type="button">リーチ</button></span>
        <span id="tumoagari_span" style="display:none;"><button id="tumoagari"" type="button">ツモ上がり</button></span>
        </footer>
        <a class="js-modal-open" href="">クリックでモーダルを表示</a>
        <div class="modal js-modal">
		<div class="modal__bg js-modal-close"></div>
		<div class="modal__content">
            <img id="agari_0" src="http://localhost/majan/public/img/hai/3p.png">
            <img id="agari_1" src="http://localhost/majan/public/img/hai/3p.png">
            <img id="agari_2" src="http://localhost/majan/public/img/hai/3p.png">
            <img id="agari_3" src="http://localhost/majan/public/img/hai/3p.png">
            <img id="agari_4" src="http://localhost/majan/public/img/hai/3p.png">
            <img id="agari_5" src="http://localhost/majan/public/img/hai/3p.png">
            <img id="agari_6" src="http://localhost/majan/public/img/hai/3p.png">
            <img id="agari_7" src="http://localhost/majan/public/img/hai/3p.png">
            <img id="agari_9" src="http://localhost/majan/public/img/hai/3p.png">
            <img id="agari_10" src="http://localhost/majan/public/img/hai/3p.png">
            <img id="agari_11" src="http://localhost/majan/public/img/hai/3p.png">
            <img id="agari_12" src="http://localhost/majan/public/img/hai/3p.png">
            <img id="agari_13" src="http://localhost/majan/public/img/hai/3p.png">&nbsp;
            <img id="agari_14" src="http://localhost/majan/public/img/hai/3p.png">
            <table>
                <td><p id="yaku_0">役</p></td>
                <td><p id="han_0">飜</p></td>
                <tr>
                    <td><p id="yaku_1"></p></td>
                    <td><p id="han_1"></p></td>
                </td>
            </table>
            <p>ここにモーダルウィンドウで表示したいコンテンツを入れます。モーダルウィンドウを閉じる場合は下の「閉じる」をクリックするか、背景の黒い部分をクリックしても閉じることができます。</p>
			<a class="js-modal-close" href="">閉じる</a>
		</div><!--modal__inner-->
	</div><!--modal-->
    </body>
</html>
