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
            setInterval(function(){
                var ck_flg = "";
                var player_tumo = "{{Session::get('player_no')}}" + "_tumo";
                if("{{$haipai->tsumo_ban}}" == "{{Session::get('player_no')}}"){
                    ck_flg = "tumo_ban";
                }
                if("{{$haipai->tsumo_ban}}" == player_tumo){
                    ck_flg = "tumo_ban";
                }
                if(ck_flg != "tumo_ban"){
                    location.reload();
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
                           location.reload();
                        }
                    })
                    // Ajaxリクエストが失敗した場合
                    .fail(function(data) {
                        alert("接続失敗");
                    });
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
                        $('#tehai_tumo').hide();
                        location.reload();
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
                if(Session::get('player_no') =="player1"){
                    if($haipai->player3_sutehai != ""){
                        $sutetehai = explode(',',$haipai->player3_sutehai);
                        array_shift($sutetehai);
                        $count = count($sutetehai);
                        if($count > 20){
                            $sutehai_cnt = 40 - $count;
                            $sutehai_sabun = 20 - $sutehai_cnt;

                            $cnt = 0;
                            echo "<tr>";
                            if($sutehai_cnt <= 20){
                                for($i = 1;$i <= $sutehai_cnt;$i++){
                                    echo "<td></td>";
                                }
                            }
                            $sutehai_data = $sutehai_img;
                            foreach($sutehai_img as $val){
                                $cnt++;
                                if($cnt <= $sutehai_sabun){
                                    array_shift($sutehai_data);
                                    $img_path= asset("/img/hai/" . $val . ".png");
                                    echo "<td><img class=\"rotate1\" src= " . $img_path . "></td>";
                                }
                            }
                            echo "</tr>";
                            echo "<tr>";
                            $sutehai_img = array_reverse($sutetehai);
                            foreach($sutehai_data as $val){
                                $img_path= asset("/img/hai/" . $val . ".png");
                                echo "<td><img class=\"rotate1\" src= " . $img_path . "></td>";
                            }
                            echo "</tr>";
                        }else{
                            echo "<tr></tr>";
                            echo "<tr>";
                            $sutehai_img = array_reverse($sutetehai);
                            foreach($sutehai_img as $val){
                                $img_path= asset("/img/hai/" . $val . ".png");
                                echo "<td><img class=\"rotate1\" src= " . $img_path . "></td>";
                            }
                            echo "</tr>";
                        }
                    }                    
                }
                if(Session::get('player_no') =="player3"){
                    if($haipai->player1_sutehai != ""){
                        $sutetehai = explode(',',$haipai->player1_sutehai);
                        array_shift($sutetehai);
                        $count = count($sutetehai);
                        $sutehai_img = array_reverse($sutetehai);

                        if($count > 20){
                            $sutehai_cnt = 40 - $count;
                            $sutehai_sabun = 20 - $sutehai_cnt;

                            $cnt = 0;
                            echo "<tr>";
                            if($sutehai_cnt <= 20){
                                for($i = 1;$i <= $sutehai_cnt;$i++){
                                    echo "<td></td>";
                                }
                            }
                            $sutehai_data = $sutehai_img;
                            foreach($sutehai_img as $val){
                                $cnt++;
                                if($cnt <= $sutehai_sabun){
                                    array_shift($sutehai_data);
                                    $img_path= asset("/img/hai/" . $val . ".png");
                                    echo "<td><img class=\"rotate1\" src= " . $img_path . "></td>";
                                }
                            }
                            echo "</tr>";
                            echo "<tr>";
                            $sutehai_img = array_reverse($sutetehai);
                            foreach($sutehai_data as $val){
                                $img_path= asset("/img/hai/" . $val . ".png");
                                echo "<td><img class=\"rotate1\" src= " . $img_path . "></td>";
                            }
                            echo "</tr>";
                        }else{
                            echo "<tr></tr>";
                            echo "<tr>";
                            $sutehai_img = array_reverse($sutetehai);
                            foreach($sutehai_img as $val){
                                if($val != null){
                                    $img_path= asset("/img/hai/" . $val . ".png");
                                    echo "<td><img class=\"rotate1\" src= " . $img_path . "></td>";
                                }
                            }
                            echo "</tr>";
                        }
                    }
                }
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
                        if(Session::get('player_no') =="player2"){
                            $sutetehai = explode(',',$haipai->player1_sutehai);
                        }
                        if(Session::get('player_no') =="player3"){
                            $sutetehai = explode(',',$haipai->player2_sutehai);
                        }
                        // 捨て配テーブル
                        // 24 18 12 6  0
                        // 25 19 13 7  1
                        // 26 20 14 8  2
                        // 27 21 15 9  3
                        // 28 22 16 10 4
                        // 29 23 17 11 5
                        array_shift($sutetehai);
                        $count = count($sutetehai);
                        $sutehai1 ="";
                        $sutehai2 ="";
                        $sutehai3 ="";
                        $sutehai4 ="";
                        $sutehai5 ="";
                        $sutehai6 ="";
                        if($haipai->player2_sutehai !=""){
                            if($count < 6){
                                $img_path = asset("/img/hai/" . $sutetehai[0] . ".png");
                                echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                if(isset($sutetehai[1])){
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[2])){
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[3])){
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[4])){
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[5])){
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                            }
                            if($count >= 6 && $count < 13){
                                $img_path = asset("/img/hai/" . $sutetehai[0] . ".png");
                                $img_path2 = asset("/img/hai/" . $sutetehai[6] . ".png");
                                echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                if(isset($sutetehai[7])){
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[7] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                                if(isset($sutetehai[8])){
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[8] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                                if(isset($sutetehai[9])){
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[9] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                                if(isset($sutetehai[10])){
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[10] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                                if(isset($sutetehai[11])){
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[11] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                            }
                            if($count > 12 && $count < 19){
                                $img_path = asset("/img/hai/" . $sutetehai[0] . ".png");
                                $img_path2 = asset("/img/hai/" . $sutetehai[6] . ".png");
                                $img_path3 = asset("/img/hai/" . $sutetehai[12] . ".png");
                                echo "<tr><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                if(isset($sutetehai[13])){
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[7] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[13] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[7] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                                if(isset($sutetehai[14])){
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[8] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[14] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[8] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                                if(isset($sutetehai[15])){
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[9] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[15] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[9] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                                if(isset($sutetehai[16])){
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[10] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[16] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[10] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                                if(isset($sutetehai[17])){
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[11] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[17] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[11] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                            }
                            if($count > 18 && $count < 28){
                                $img_path = asset("/img/hai/" . $sutetehai[0] . ".png");
                                $img_path2 = asset("/img/hai/" . $sutetehai[6] . ".png");
                                $img_path3 = asset("/img/hai/" . $sutetehai[12] . ".png");
                                $img_path4 = asset("/img/hai/" . $sutetehai[18] . ".png");
                                echo "<tr><td class=\"td2\"><img class=\"rotate\" src= " . $img_path4 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                if(isset($sutetehai[19])){
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[7] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[13] . ".png");
                                    $img_path4 = asset("/img/hai/" . $sutetehai[19] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate\" src= " . $img_path4 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[7] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[13] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                                if(isset($sutetehai[20])){
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[8] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[14] . ".png");
                                    $img_path4 = asset("/img/hai/" . $sutetehai[20] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate\" src= " . $img_path4 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[8] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[14] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                                if(isset($sutetehai[21])){
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[9] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[15] . ".png");
                                    $img_path4 = asset("/img/hai/" . $sutetehai[21] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate\" src= " . $img_path4 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[9] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[15] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                                if(isset($sutetehai[22])){
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[10] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[16] . ".png");
                                    $img_path4 = asset("/img/hai/" . $sutetehai[22] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate\" src= " . $img_path4 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[10] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[16] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                                if(isset($sutetehai[23])){
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[11] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[17] . ".png");
                                    $img_path4 = asset("/img/hai/" . $sutetehai[23] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate\" src= " . $img_path4 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[11] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[17] . ".png");
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate\" src= " . $img_path . "></td></tr>";
                                }
                            }
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
                        if(Session::get('player_no') =="player1"){
                            $sutetehai = explode(',',$haipai->player2_sutehai);
                        }
                        if(Session::get('player_no') =="player2"){
                            $sutetehai = explode(',',$haipai->player3_sutehai);
                        }
                        // 捨て配テーブル
                        // 5 11 17 23 29
                        // 4 10 16 22 28
                        // 3  9 15 21 27
                        // 2  8 14 20 26
                        // 1  7 13 19 25
                        // 0  6 12 18 24
                        array_shift($sutetehai);
                        $count = count($sutetehai);
                        $sutehai1 ="";
                        $sutehai2 ="";
                        $sutehai3 ="";
                        $sutehai4 ="";
                        $sutehai5 ="";
                        $sutehai6 ="";
                        if($haipai->player2_sutehai !=""){
                            if($count < 6){
                                if(isset($sutetehai[5])){
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }else{
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[3])){
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }else{
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[2])){
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }else{
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[1])){
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }else{
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[0])){
                                    $img_path = asset("/img/hai/" . $sutetehai[0] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }else{
                                    echo "<tr><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                            }
                            if($count >= 6 && $count < 13){
                               if(isset($sutetehai[11])){
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[11] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[10])){
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[10] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[9])){
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[9] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[8])){
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[8] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[7])){
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[7] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                $img_path = asset("/img/hai/" . $sutetehai[0] . ".png");
                                $img_path2 = asset("/img/hai/" . $sutetehai[6] . ".png");
                                echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                            }
                            if($count > 12 && $count < 19){
                                if(isset($sutetehai[17])){
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[11] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[17] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[11] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[16])){
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[10] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[16] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[10] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[15])){
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[9] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[15] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[9] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[14])){
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[8] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[14] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[8] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[13])){
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[7] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[13] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[7] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"></td><td class=\"td2\"></td></tr>";
                                }
                                $img_path = asset("/img/hai/" . $sutetehai[0] . ".png");
                                $img_path2 = asset("/img/hai/" . $sutetehai[6] . ".png");
                                $img_path3 = asset("/img/hai/" . $sutetehai[12] . ".png");
                                echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"></td></tr>";
                            }
                            if($count > 18 && $count < 28){
                                if(isset($sutetehai[23])){
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[11] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[17] . ".png");
                                    $img_path4 = asset("/img/hai/" . $sutetehai[23] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path4 . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[5] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[11] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[17] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[22])){
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[10] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[16] . ".png");
                                    $img_path4 = asset("/img/hai/" . $sutetehai[22] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path4 . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[4] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[10] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[16] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[21])){
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[9] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[15] . ".png");
                                    $img_path4 = asset("/img/hai/" . $sutetehai[21] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path4 . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[3] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[9] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[15] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[20])){
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[8] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[14] . ".png");
                                    $img_path4 = asset("/img/hai/" . $sutetehai[20] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path4 . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[2] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[8] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[14] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"></td></tr>";
                                }
                                if(isset($sutetehai[19])){
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[7] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[13] . ".png");
                                    $img_path4 = asset("/img/hai/" . $sutetehai[19] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path4 . "></td></tr>";
                                }else{
                                    $img_path = asset("/img/hai/" . $sutetehai[1] . ".png");
                                    $img_path2 = asset("/img/hai/" . $sutetehai[7] . ".png");
                                    $img_path3 = asset("/img/hai/" . $sutetehai[13] . ".png");
                                    echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"></td></tr>";
                                }
                                $img_path = asset("/img/hai/" . $sutetehai[0] . ".png");
                                $img_path2 = asset("/img/hai/" . $sutetehai[6] . ".png");
                                $img_path3 = asset("/img/hai/" . $sutetehai[12] . ".png");
                                $img_path4 = asset("/img/hai/" . $sutetehai[18] . ".png");
                                echo "<tr><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path2 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path3 . "></td><td class=\"td2\"><img class=\"rotate2\" src= " . $img_path4 . "></td></tr>";
                            }
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
                        if(Session::get('player_no') =="player1"){
                            $sutetehai = explode(',',$haipai->player1_sutehai);
                        }
                        if(Session::get('player_no') =="player2"){
                            $sutetehai = explode(',',$haipai->player2_sutehai);
                        }
                        if(Session::get('player_no') =="player3"){
                            $sutetehai = explode(',',$haipai->player3_sutehai);
                        }
                        array_shift($sutetehai);
                        $count = count($sutetehai);
                        if($count > 20){
                            $cnt = 0;
                            echo "<tr>";
                            foreach($sutetehai as $val){
                                $cnt++;
                                $img_path= asset("/img/hai/" . $val . ".png");
                                echo "<td><img src= " . $img_path . "></td>";
                                if($cnt == 20){
                                    echo "</tr>";
                                    echo "<tr>";
                                }
                            }
                            
                            echo "</tr>";
                        }else{
                            echo "<tr>";
                            foreach($sutetehai as $val){
                                if($val != null){
                                    $img_path= asset("/img/hai/" . $val . ".png");
                                    echo "<td><img src= " . $img_path . "></td>";
                                }
                            }
                            echo "</tr>";
                            echo "<tr></tr>";
                        }
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
                $cnt++;
                $id = "id=\"tehai_" . $cnt . "\""; 
                $img_path= asset("/img/hai/" . $val . ".png");
                echo "<img " . $id . "value= \"". $val . "\" src= " . $img_path . ">";
            }
            // ツモ牌
            echo "<img style=\"display:none;\" id=\"tehai_tumo\" value= \"". $val . "\" src= " . $img_path . ">";
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
        @if($haipai->tsumo_ban == Session::get('player_no'))
            <button id="tumo" type="button">ツモ</button>
        @endif
        <button id="lon" type="button">ロン</button>
        <button id="pon" type="button">ポン</button>
        <button id="kan" type="button">カン</button>
        <button id="haitumo" type="button">牌ツモ</button>
        </footer>
    </body>
</html>
