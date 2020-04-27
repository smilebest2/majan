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
            var update_time = "{{$haipai->update_time}}";
            setInterval(function(){
                if(ck_flg != "tumo_ban"){
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
                                var player = "{{Session::get('player_no')}}";
                                if(data.message.tsumo_ban == player){
                                    $('#tumo_span').show();
                                    ck_flg = "tumo_ban";
                                }
                                if(player == "player1"){
                                    var hai_data = data.message.player1_hai;
                                    var hai_arr = hai_data.split(',');
                                }
                                if(player == "player2"){
                                    var hai_data = data.message.player2_hai;
                                    var hai_arr = hai_data.split(',');
                                }
                                if(player == "player3"){
                                    var hai_data = data.message.player3_hai;
                                    var hai_arr = hai_data.split(',');
                                }
                                for(var i=0; i < hai_arr.length; i++){
                                    var select_id = "#tehai_" + i;
                                    var img_path = $(select_id).attr('src');
                                    var path = img_path.slice(0,-6);
                                    var new_path = path + hai_arr[i] + ".png";
                                    $(select_id).attr('src', new_path);
                                }
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
                                    for(var i=0; i < sutehai_arr.length; i++){
                                        var select_id = sutehai_disp + i;
                                        var img_path = $(select_id).attr('src');
                                        var path = img_path.slice(0,-6);
                                        var new_path = path + sutehai_arr[i] + ".png";
                                        $(select_id).attr('src', new_path);
                                    }
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
                                    for(var i=0; i < sutehai_arr.length; i++){
                                        var select_id = sutehai_disp + i;
                                        var img_path = $(select_id).attr('src');
                                        var path = img_path.slice(0,-6);
                                        var new_path = path + sutehai_arr[i] + ".png";
                                        $(select_id).attr('src', new_path);
                                    }
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
                                    for(var i=0; i < sutehai_arr.length; i++){
                                        var select_id = sutehai_disp + i;
                                        var img_path = $(select_id).attr('src');
                                        var path = img_path.slice(0,-6);
                                        var new_path = path + sutehai_arr[i] + ".png";
                                        $(select_id).attr('src', new_path);
                                    }
                                }
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
                    if($('#tehai_tumo').attr('value') != "0h"){
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
                            data:{'sutehai':sutehai,'tumohai':tumohai},
                            dataType:'json'
                        })
                        // Ajaxリクエストが成功した場合
                        .done(function(data) {
                            if (data.result == "OK") {
                                ck_flg = "";
                                $('#tehai_tumo').hide();
                            }
                        })
                        // Ajaxリクエストが失敗した場合
                        .fail(function(data) {
                            alert("接続失敗");
                        });
                    }
                });
            }
            $('#tehai_tumo').on('click', function() {
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
                    data:{'sutehai':sutehai,'tumohai':tumohai},
                    dataType:'json'
                })
                // Ajaxリクエストが成功した場合
                .done(function(data) {
                    if (data.result == "OK") {
                        $('#tumo_span').hide();
                        $('#tehai_tumo').hide();
                        ck_flg = "";
                    }
                })
                // Ajaxリクエストが失敗した場合
                .fail(function(data) {
                    alert("接続失敗");
                });
            });
            $('#tumo').on('click', function() {
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
                        var img_path = $('#tehai_tumo').attr('src');
                        var path = img_path.slice(0,-6);
                        var new_path = path + data.message + ".png";
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
                        ck_flg = "";
                })
                // Ajaxリクエストが失敗した場合
                .fail(function(data) {
                    alert("接続失敗");
                });
            });
        });
        </script>
    </head>
    <body>
        <header class="header">
        @if(Session::get('player_no') =="player1")
        {{$game_status->user3}} 持ち点 {{$game_status->player3_ten}}<br>
        @endif
        @if(Session::get('player_no') =="player3")
        {{$game_status->user1}} 持ち点 {{$game_status->player1_ten}}<br>
        @endif
        @if(Session::get('player_no') =="player1" || Session::get('player_no') =="player3")
            <?php
                //対面鳴き牌
                if(Session::get('player_no') =="player1"){
                    $nakihai = explode(',',$haipai->player3_nakihai);
                    foreach($nakihai as $val){
                        if($val != null){
                            $img_path= asset("/img/hai/" . $val . ".png");
                            echo "<img class=\"rotate1\"src= " . $img_path . ">";
                        }
                    }
                }
                if(Session::get('player_no') =="player3"){
                    $nakihai = explode(',',$haipai->player1_nakihai);
                    foreach($nakihai as $val){
                        if($val != null){
                            $img_path= asset("/img/hai/" . $val . ".png");
                            echo "<img class=\"rotate1\"src= " . $img_path . ">";
                        }
                    }
                }
            ?>
            <?php
                //対面手牌
                if(Session::get('player_no') =="player1"){
                    $tehai = explode(',',$haipai->player3_hai);
                    foreach($tehai as $val){
                        $img_path= asset("/img/hai/yoko.png");
                        echo "<img class=\"rotate1\" src= " . $img_path . ">";
                    }
                }
                if(Session::get('player_no') =="player3"){
                    $tehai = explode(',',$haipai->player1_hai);
                    foreach($tehai as $val){
                        $img_path= asset("/img/hai/yoko.png");
                        echo "<img class=\"rotate1\" src= " . $img_path . ">";
                    }
                }
            ?><br>
        @endif
        </header>
        <main class="main-wrap">
        <aside class="side">
        {{-- 上家 --}}
            @if(Session::get('player_no') =="player2")
                {{$game_status->user1}}<br>
                 持ち点 {{$game_status->player1_ten}}
            @endif
            @if(Session::get('player_no') =="player3")
                {{$game_status->user2}}<br>
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
                @if(Session::get('player_no') =="player2" || Session::get('player_no') =="player3")
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
                @endif
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
            <font color="#ff9999">{{$ba}}&nbsp;{{$kyoku[1]}}局&nbsp;&nbsp;{{$kyoku[2]}}本場</font><br>
            <font color="#ff9999">残り牌{{count($nokori)}}</font><br>
            <?php echo $dorayama; ?> 
            </article>
            {{-- 下家捨て配 --}}
            <article class="c_side">
                <div>
                    @if(Session::get('player_no') =="player1" || Session::get('player_no') =="player2")
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
                @endif
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
        <aside class="side">
            @if(Session::get('player_no') =="player1" || Session::get('player_no') =="player2")
                @if(Session::get('player_no') =="player1")
                    {{$game_status->user2}}<br>
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
                    {{$game_status->user3}}<br>
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
        <?php
        //鳴き牌
            if(Session::get('player_no') =="player1"){
                $tehai = explode(',',$haipai->player1_nakihai);
            }
            if(Session::get('player_no') =="player2"){
                $tehai = explode(',',$haipai->player2_nakihai);
            }
            if(Session::get('player_no') =="player3"){
                $tehai = explode(',',$haipai->player3_nakihai);
            }
            foreach($tehai as $val){
                if($val != null){
                    $img_path= asset("/img/hai/" . $val . ".png");
                    echo "<img src= " . $img_path . ">";
                }
            }
        ?>
        <br><br>

        <span id="tumo_span" style="display:none;"><button id="tumo" type="button">ツモ</button></span>
        <span><button id="lon" type="button">ロン</button></span>
        <span><button id="pon" type="button">ポン</button></span>
        <span><button id="kan" type="button">カン</button></span>
        <span><button id="haitumo" type="button">牌ツモ</button></span>
        </footer>
    </body>
</html>
