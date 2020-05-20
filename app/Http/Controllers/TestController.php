<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class TestController extends Controller
{

    //マンズ関連
    public $manMentsuMax = 0;
    public $manTaatsuMax = 0;

    //ピンズ関連
    public $pinMentsuMax = 0;
    public $pinTaatsuMax = 0;

    //ソーズ関連
    public $souMentsuMax = 0;
    public $souTaatsuMax = 0;

    //字牌関連
    public $jiTaatsuMax = 0;

    public $preMentsuCount = 0;//kanzen_koutsu_suuとkanzen_shuntsu_suu格納用
    public $koutsuCount = 0;//コーツ数カウント用
    public $adjustment = 0;
    public $tempTehai = Array();
    public $toitsu_suu=0;//トイツ数
    public $koutsu_suu=0;//コーツ数
    public $shuntsu_suu=0;//シュンツ数
    public $taatsu_suu=0;//ターツ数
    public $mentsu_suu=0;//メンツ数
    public $kanzen_koutsu_suu=0;//完全コーツ数
    public $kanzen_shuntsu_suu=0;//完全シュンツ数
    public $syanten_temp=0;//シャンテン数（計算用）
    public $syanten_normal=8;//シャンテン数（結果用）
    public $kanzen_Koritsu_suu=0;//完全孤立牌数

    public function index (Request $request) 
    {
        $wait="";
        $game_serch = DB::table('game_status')->get();
        $no_user = NULL;
        $user1 = NULL;
        $user2 = NULL;

        foreach($game_serch as $val){
            if($val->status == 0){
                $no_user = "not_null";
                $game_id = $val->id;
                $user1 = $val->user1;
                $user2 = $val->user2;
            }
        }
        if($no_user == NULL){
            DB::table('game_status')->insert([
                'user1' => $request->session()->get('user'),
            ]);
            $game_id = DB::getPdo()->lastInsertId();
            $request->session()->put('game_id', $game_id);
            $request->session()->put('player_no', 'player1');
        }
        if($user1 != NULL && $user2 == NULL){
            DB::table('game_status')->where('id',$game_id)->update([
                'user2' => $request->session()->get('user'),
            ]);
            $request->session()->put('game_id', $game_id);
            $request->session()->put('player_no', 'player2');
        }
        if($user2 != NULL){
            DB::table('game_status')->where('id',$game_id)->update([
                'user3' => $request->session()->get('user'),
                'status' => '1',
            ]);
            $request->session()->put('game_id', $game_id);
            $request->session()->put('player_no', 'player3');
            $haipai_id = $this->haipai($game_id);
            $wait = "wait";
        }

        return view('ready');
    }
    public function readycheck (Request $request) 
    {
        $game_status = DB::table('game_status')->where('id',$request->session()->get('game_id'))->first();

        if($game_status->status == 1){
            $res = ['result'=>'OK','message'=>'OK'];
            $result = json_encode($res);
            return $result;
        }else{
            $res = ['result'=>'NG','message'=>'NG'];
            $result = json_encode($res);
            return $result;
        }
    }
    public function tumo (Request $request) 
    {
        $game_status = DB::table('game_status')->where('id',$request->session()->get('game_id'))->first();
        $haipai = DB::table('haipai')->where('game_id',$request->session()->get('game_id'))->first();
        $tumo_player = substr($haipai->tsumo_ban, 0, 7);
        if($request->session()->get('player_no') == $tumo_player){
            $haipai_nokorihai = explode(',',$haipai->nokori_hai);

            $nokorihai = array_shift($haipai_nokorihai);
            $haipai_data = "";
            foreach($haipai_nokorihai as $val){
                $haipai_data .= $val .",";
            }
            $nokori_data = substr($haipai_data, 0, -1);
            $tumohai = substr($haipai->nokori_hai,0,2);

            $result = DB::table('haipai')
            ->where('game_id', $request->session()->get('game_id'))
            ->update([
                'nokori_hai'=> $nokori_data,
                'tsumo_ban'=> $request->session()->get('player_no') . "_tumo"
            ]);
            if($request->session()->get('player_no') == "player1"){
                $player_hai = $haipai->player1_hai;
                $player_nakihai = $haipai->player1_nakihai;
                $player_reach = $haipai->player1_reach;
            }
            if($request->session()->get('player_no') == "player2"){
                $player_hai = $haipai->player2_hai;
                $player_nakihai = $haipai->player2_nakihai;
                $player_reach = $haipai->player2_reach;
            }
            if($request->session()->get('player_no') == "player3"){
                $player_hai = $haipai->player3_hai;
                $player_nakihai = $haipai->player3_nakihai;
                $player_reach = $haipai->player3_reach;
            }
            if($player_nakihai != ""){
                if(strpos($player_nakihai, ',') == false){
                    $p_nakihai = $player_nakihai;
                    if(substr($p_nakihai, 2, 1) == "p"){
                        $nakihai = substr($p_nakihai, 3, 2) . "," . substr($p_nakihai, 3, 2) . "," . substr($p_nakihai, 3, 2);
                    }
                    if(substr($p_nakihai, 2, 1) == "k"){
                        $nakihai = substr($p_nakihai, 3, 2) . "," . substr($p_nakihai, 3, 2) . "," . substr($p_nakihai, 3, 2) . "," . substr($val, 3, 2);
                    }
                }else{
                    $p_nakihai = explode(',',$player_nakihai);
                    foreach($p_nakihai as $val){
                        if(substr($val, 2, 1) == "p"){
                            $nakihai .= "," . substr($val, 3, 2) . "," . substr($val, 3, 2) . "," . substr($val, 3, 2);
                        }
                        if(substr($val, 2, 1) == "k"){
                            $nakihai .= "," . substr($val, 3, 2) . "," . substr($val, 3, 2) . "," . substr($val, 3, 2) . "," . substr($val, 3, 2);
                        }
                    }
                }
                Log::debug();
                $p_hai = $player_hai . "," . $tumohai . $nakihai;
            }else{
                $p_hai = $player_hai . "," . $tumohai;
            }
            $p_hai = $player_hai . "," . $tumohai;
            $request->session()->put('tumohai', $tumohai);

            $tenpai = "";
            if($player_nakihai == ""){
                $tenpai = "tenpai";
/*
                $tempai = $this->titoicheck($p_hai);
                if($tempai == ""){
                    $tempai = $this->kokusicheck($p_hai);
                }
                if($tempai == ""){
                    $tempai = $this->tenpaicheck($p_hai);
                }
*/
            }
        }else{
            $user_no = "user" . substr($tumo_player, -1);
            $message = $game_status->$user_no . "が鳴きました";
            $res = ['result'=>'NG','message'=>$message];
            $result = json_encode($res);
            return $result;
        }
        
        if($game_status->status == 1){
            $res = ['result'=>'OK','message'=>$tumohai,'tenpai'=>$tenpai,'reach'=>$player_reach];
            $result = json_encode($res);
            return $result;
        }else{
            $res = ['result'=>'NG','message'=>'NG'];
            $result = json_encode($res);
            return $result;
        }
    }
    public function sutehai (Request $request) 
    {
        $haipai = DB::table('haipai')->where('game_id',$request->session()->get('game_id'))->first();
        if($request->session()->get('player_no') == "player1"){
            $player_sutehai = $haipai->player1_sutehai;
            $player_hai = explode(',',$haipai->player1_hai);
        }
        if($request->session()->get('player_no') == "player2"){
            $player_sutehai = $haipai->player2_sutehai;
            $player_hai = explode(',',$haipai->player2_hai);
        }
        if($request->session()->get('player_no') == "player3"){
            $player_sutehai = $haipai->player3_sutehai;
            $player_hai = explode(',',$haipai->player3_hai);
        }
        if($request['reach'] == "reach"){
            $reach = "r";
        }else{
            $reach = "";
        }
        if($player_sutehai !=""){
            $sutehai_data = $player_sutehai . "," . $reach . $request['sutehai'];
        }else{
            $sutehai_data = $reach . $request['sutehai'];
        }
        if($request['tumohai'] != "" || $request['ponkan'] == "ponkan"){
            $dupe = "";
            $hai_data = "";
            foreach($player_hai as $val){
                if($val == $request['sutehai'] && $dupe == ""){
                    $dupe = "dupe";
                    if($request['ponkan'] != "ponkan"){
                        $hai_data .= $request['tumohai'] . ",";
                    }
                }else{
                    $hai_data .= $val . ",";
                }
            }
            $p_hai_data = substr($hai_data, 0, -1);
            $player_hai = ltrim($p_hai_data, ',');
            $p_hai = $this->seiretu($player_hai);
        }else{
            if($request->session()->get('player_no') == "player1"){
                $p_hai = $haipai->player1_hai;
            }
            if($request->session()->get('player_no') == "player2"){
                $p_hai = $haipai->player2_hai;
            }
            if($request->session()->get('player_no') == "player3"){
                $p_hai = $haipai->player3_hai;
            }
        }

        $sutehai = $request->session()->get('player_no') . "," . $request['sutehai'];
        if($request->session()->get('player_no') == "player1"){
            $result = DB::table('haipai')
            ->where('game_id', $request->session()->get('game_id'))
            ->update([
                'player1_hai'=>$p_hai,
                'player1_sutehai'=>$sutehai_data,
                'tsumo_ban'=> "player2",
                'sutehai'=> $sutehai,
                'update_time' => Carbon::now()
            ]);
        }
        if($request->session()->get('player_no') == "player2"){
            $result = DB::table('haipai')
            ->where('game_id', $request->session()->get('game_id'))
            ->update([
                'player2_hai'=>$p_hai,
                'player2_sutehai'=>$sutehai_data,
                'tsumo_ban'=> "player3",
                'sutehai'=> $sutehai,
                'update_time' => Carbon::now()
            ]);
        }
        if($request->session()->get('player_no') == "player3"){
            $result = DB::table('haipai')
            ->where('game_id', $request->session()->get('game_id'))
            ->update([
                'player3_hai'=>$p_hai,
                'player3_sutehai'=>$sutehai_data,
                'tsumo_ban'=> "player1",
                'sutehai'=> $sutehai,
                'update_time' => Carbon::now()
            ]);
        }
        
        $haipai = DB::table('haipai')->where('game_id',$request->session()->get('game_id'))->first();
        $haipai_data = array();
        if($request->session()->get('player_no') == "player1"){
            $haipai_data['player1_hai'] = $haipai->player1_hai;
        }
        if($request->session()->get('player_no') == "player2"){
            $haipai_data['player2_hai'] = $haipai->player2_hai;
        }
        if($request->session()->get('player_no') == "player3"){
            $haipai_data['player3_hai'] = $haipai->player3_hai;
        }
        $nokori = explode(',',$haipai->nokori_hai);
        $haipai_data['player1_sutehai'] = $haipai->player1_sutehai;
        $haipai_data['player1_nakihai'] = $haipai->player1_nakihai;
        $haipai_data['player1_reach'] = $haipai->player1_reach;
        $haipai_data['player1_ponkan'] = $haipai->player1_ponkan;
        $haipai_data['player2_sutehai'] = $haipai->player2_sutehai;
        $haipai_data['player2_nakihai'] = $haipai->player2_nakihai;
        $haipai_data['player2_reach'] = $haipai->player2_reach;
        $haipai_data['player2_ponkan'] = $haipai->player2_ponkan;
        $haipai_data['player3_sutehai'] = $haipai->player3_sutehai;
        $haipai_data['player3_nakihai'] = $haipai->player3_nakihai;
        $haipai_data['player3_reach'] = $haipai->player3_reach;
        $haipai_data['player3_ponkan'] = $haipai->player3_ponkan;
        $haipai_data['nokori_hai'] = count($nokori);
        $haipai_data['tsumo_ban'] = $haipai->tsumo_ban;

        if($result){
            $res = ['result'=>'OK','message'=>$haipai_data];
            $result = json_encode($res);
            return $result;
        }else{
            $res = ['result'=>'NG','message'=>'NG'];
            $result = json_encode($res);
            return $result;
        }
    }
    public function start (Request $request) 
    {
        $game_status = DB::table('game_status')->where('id',$request->session()->get('game_id'))->first();
        $haipai = DB::table('haipai')->where('game_id',$request->session()->get('game_id'))->first();

        return view('test',compact('game_status','haipai'));
    }
    private function haipai($game_id){
        $paiyama = config('const.paiYama');

        $keys = array_keys($paiyama);
        shuffle($keys);
        $random = array();
        $array_no ="";
        foreach ($keys as $key) {
            $array_no .= strval($key) . ",";
            $random[$key] = $paiyama[$key];
        }
        $haipai = substr($array_no, 0, -1); 

        $yamahai="";
        //ドラ山牌
        for($i = 1;$i <= 14;$i++){
            $sento_hai = array_shift($random);
            $yamahai .= $sento_hai .",";
        }
        $dora_yama = substr($yamahai, 0, -1);
        //配牌
        $yamahai = "";
        $player1 = "";
        $player2 = "";
        $player3 = "";
        for($i = 1;$i <= 39;$i++){
            $sento_hai = array_shift($random);
            if($i <= 13){
                $player1 .= $sento_hai . ",";
            }
            if($i > 13 && $i <= 26){
                $player2 .= $sento_hai . ",";
            }
            if($i > 26 && $i <= 39){
                $player3 .= $sento_hai . ",";
            }
            $yamahai .= $sento_hai .",";
        }
        $player1_hai_data = substr($player1, 0, -1);
        $player2_hai_data = substr($player2, 0, -1);
        $player3_hai_data = substr($player3, 0, -1);
        $nokori_hai = substr($yamahai, 0, -1);
        $player1_hai = $this->seiretu($player1_hai_data);
        $player2_hai = $this->seiretu($player2_hai_data);
        $player3_hai = $this->seiretu($player3_hai_data);
        $nokorihai_str = "";
        foreach ($random as $val) {
            $nokorihai_str .= $val . ",";
        }
        $nokori_hai = substr($nokorihai_str, 0, -1);
        DB::table('haipai')->insert([
            'game_id' => $game_id,
            'haipai' => $haipai,
            'player1_hai' => $player1_hai,
            'player1_sutehai' => "",
            'player1_nakihai' => "",
            'player1_reach' => "",
            'player1_ponkan' => "",
            'player2_hai' => $player2_hai,
            'player2_sutehai' => "",
            'player2_nakihai' => "",
            'player2_reach' => "",
            'player2_ponkan' => "",
            'player3_hai' => $player3_hai,
            'player3_sutehai' => "",
            'player3_nakihai' => "",
            'player3_reach' => "",
            'player3_ponkan' => "",
            'dorayama_hai' => $dora_yama,
            'nokori_hai' => $nokori_hai,
            'tsumo_ban' => "player1",
            'update_time' => now()
        ]);
        $haipai_id = DB::getPdo()->lastInsertId();
        DB::table('game_status')->where('id',$game_id)->update([
            'oya_ban' => 'player1',
            'kyoku' => 't,1,0',
            'player1_ten' => '35000',
            'player2_ten' => '35000',
            'player3_ten' => '35000',
        ]);
        return $haipai_id;
    }
    public function seiretu($player_hai_data) 
    {
        $hai = explode(',',$player_hai_data);
        $hai_data = "";
        sort($hai);
        foreach($hai as $val){
            $num = substr($val, 0, 1);
            $type = substr($val, 1, 1);
            if($type == "p"){
                $hai_data .= $val . ",";
            }
        }
        foreach($hai as $val){
            $num = substr($val, 0, 1);
            $type = substr($val, 1, 1);
            if($type == "s"){
                $hai_data .= $val . ",";
            }
        }
        foreach($hai as $val){
            $num = substr($val, 0, 1);
            $type = substr($val, 1, 1);
            if($type == "z"){
                $hai_data .= $val . ",";
            }
        }
        foreach($hai as $val){
            $num = substr($val, 0, 1);
            $type = substr($val, 1, 1);
            if($type == "m"){
                $hai_data .= $val . ",";
            }
        }
        $player_hai = ltrim($hai_data, ',');
        $p_hai = substr($player_hai, 0, -1);
        return $p_hai;
    }
    public function gamecheck(Request $request) 
    {
        $game_status = DB::table('game_status')->where('id',$request->session()->get('game_id'))->first();
        $haipai = DB::table('haipai')->where('game_id',$request->session()->get('game_id'))->first();

        $sutehai_player = substr($haipai->sutehai, 0, 7);
        $new_sutehai = substr($haipai->sutehai, -2);

        $haipai_data = array();
        $ponkan = array(
            'pon' => "",
            'kan' => "",
        );

        if($request->session()->get('player_no') == "player1"){
            $haipai_data['player1_hai'] = $haipai->player1_hai;
            if($request->session()->get('player_no') != $sutehai_player){
                $ponkan = $this->ponkancheck($haipai->player1_hai,$new_sutehai);
            }
        }
        if($request->session()->get('player_no') == "player2"){
            $haipai_data['player2_hai'] = $haipai->player2_hai;
            if($request->session()->get('player_no') != $sutehai_player){
                $ponkan = $this->ponkancheck($haipai->player2_hai,$new_sutehai);
            }
        }
        if($request->session()->get('player_no') == "player3"){
            $haipai_data['player3_hai'] = $haipai->player3_hai;
            if($request->session()->get('player_no') != $sutehai_player){
                $ponkan = $this->ponkancheck($haipai->player3_hai,$new_sutehai);
            }
        }
        $p1_hai = explode(',',$haipai->player1_hai);
        $p2_hai = explode(',',$haipai->player2_hai);
        $p3_hai = explode(',',$haipai->player3_hai);
        $diff = strtotime(Carbon::now()) - strtotime($haipai->update_time);
        if($diff > 2){
            $tsumo_ban = $haipai->tsumo_ban;
        }else{
            $tsumo_ban = "";
        }
        $nokori = explode(',',$haipai->nokori_hai);
        $haipai_data['player1_hai'] = count($p1_hai);
        $haipai_data['player1_sutehai'] = $haipai->player1_sutehai;
        $haipai_data['player1_nakihai'] = $haipai->player1_nakihai;
        $haipai_data['player1_reach'] = $haipai->player1_reach;
        $haipai_data['player1_ponkan'] = $haipai->player1_ponkan;
        $haipai_data['player2_hai'] = count($p2_hai);
        $haipai_data['player2_sutehai'] = $haipai->player2_sutehai;
        $haipai_data['player2_nakihai'] = $haipai->player2_nakihai;
        $haipai_data['player2_reach'] = $haipai->player2_reach;
        $haipai_data['player2_ponkan'] = $haipai->player2_ponkan;
        $haipai_data['player3_hai'] = count($p3_hai);
        $haipai_data['player3_sutehai'] = $haipai->player3_sutehai;
        $haipai_data['player3_nakihai'] = $haipai->player3_nakihai;
        $haipai_data['player3_reach'] = $haipai->player3_reach;
        $haipai_data['player3_ponkan'] = $haipai->player3_ponkan;
        $haipai_data['nokori_hai'] = count($nokori);
        $haipai_data['tsumo_ban'] = $tsumo_ban;
        $haipai_data['pon'] = $ponkan['pon'];
        $haipai_data['kan'] = $ponkan['kan'];
        
//        if($request['update_time'] != $haipai->update_time){
            $res = ['result'=>'OK','message'=>$haipai_data];
//        }else{
//            $res = ['result'=>'other_time','message'=>"not_change"];
//        }
        $result = json_encode($res);
        return $result;
    }
    public function ponkancheck($player_hai_data,$new_sutehai) 
    {
        $hai = explode(',',$player_hai_data);
        $dupe_arr = array();
        $kan_arr = array();
        $pon = "";
        $kan = "";
        $maehai = "";
        $same = "";
        $result = array();

        foreach($hai as $val){
            // 2枚同じで捨て配と同じか
            if($maehai == $val){
                if($val == $new_sutehai && $same == ""){
                    $same = "same2";
                    $pon .= $val . ",";
                }else{
                    if($val == $new_sutehai && $same == "same2"){
                        $kan .= $val . ",";
                        $same = "";
                    }else{
                        $same = "";
                    }
                }
            }
            $maehai = $val;
        }
        $result['pon'] = $pon;
        $result['kan'] = $kan;

        return $result;
    }
    public function pon(Request $request) 
    {
        $game_status = DB::table('game_status')->where('id',$request->session()->get('game_id'))->first();
        $haipai = DB::table('haipai')->where('game_id',$request->session()->get('game_id'))->first();
        
        if($request->session()->get('player_no') == "player1"){
            $haipai_data = $haipai->player1_hai;
            $nakihai_data = $haipai->player1_nakihai;
            if(substr($haipai->sutehai, 0, 7) == "player2"){
                $pon_hainomuki = "sp";
            }
            if(substr($haipai->sutehai, 0, 7) == "player3"){
                $pon_hainomuki = "tp";
            }
        }
        if($request->session()->get('player_no') == "player2"){
            $haipai_data = $haipai->player2_hai;
            $nakihai_data = $haipai->player2_nakihai;
            if(substr($haipai->sutehai, 0, 7) == "player1"){
                $pon_hainomuki = "kp";
            }
            if(substr($haipai->sutehai, 0, 7) == "player3"){
                $pon_hainomuki = "sp";
            }
        }
        if($request->session()->get('player_no') == "player3"){
            $haipai_data = $haipai->player3_hai;
            $nakihai_data = $haipai->player3_nakihai;
            if(substr($haipai->sutehai, 0, 7) == "player1"){
                $pon_hainomuki = "tp";
            }
            if(substr($haipai->sutehai, 0, 7) == "player2"){
                $pon_hainomuki = "kp";
            }
        }
        $tumo_ban = $request->session()->get('player_no') . "_tumo";
        $p_hai = $request->session()->get('player_no') . '_hai';
        $p_nakihai = $request->session()->get('player_no') . '_nakihai';

        $sutehai_player = substr($haipai->sutehai, 0, 7);
        $new_sutehai = substr($haipai->sutehai, -2);

        if($nakihai_data == ""){
            $nakihai = $pon_hainomuki . $new_sutehai;
        }else{
            $nakihai = $nakihai_data . "," . $pon_hainomuki . $new_sutehai;
        }

        $hai = explode(',',$haipai_data);
        $dupe_cnt = 0;
        $hai_data = "";
        foreach($hai as $val){
            if($val == $new_sutehai && $dupe_cnt < 2){
                $dupe_cnt++;
            }else{
                $hai_data .= $val . ",";
                $dupe_cnt = 0;
            }
        }

        $hai = substr($hai_data, 0, -1);
        $player_sutehai = $sutehai_player . '_sutehai';
        if(strpos($haipai->$player_sutehai,',') === false){
            $sutehai = substr($haipai->$player_sutehai, 0, -2);
        }else{
            $sutehai = substr($haipai->$player_sutehai, 0, -3);
        }
        $p_sutehai = $sutehai_player . '_sutehai';
        $result = DB::table('haipai')
            ->where('game_id', $request->session()->get('game_id'))
            ->update([
                $p_hai => $hai,
                $p_nakihai => $nakihai,
                $p_sutehai => $sutehai,
                'tsumo_ban' => $tumo_ban,
            ]);
        $haipai = DB::table('haipai')->where('game_id',$request->session()->get('game_id'))->first();
        $haipai_data = array();
        if($request->session()->get('player_no') == "player1"){
            $haipai_data['player1_hai'] = $haipai->player1_hai;
        }
        if($request->session()->get('player_no') == "player2"){
            $haipai_data['player2_hai'] = $haipai->player2_hai;
        }
        if($request->session()->get('player_no') == "player3"){
            $haipai_data['player3_hai'] = $haipai->player3_hai;
        }
        $nokori = explode(',',$haipai->nokori_hai);
        $haipai_data['player1_sutehai'] = $haipai->player1_sutehai;
        $haipai_data['player1_nakihai'] = $haipai->player1_nakihai;
        $haipai_data['player1_reach'] = $haipai->player1_reach;
        $haipai_data['player1_ponkan'] = $haipai->player1_ponkan;
        $haipai_data['player2_sutehai'] = $haipai->player2_sutehai;
        $haipai_data['player2_nakihai'] = $haipai->player2_nakihai;
        $haipai_data['player2_reach'] = $haipai->player2_reach;
        $haipai_data['player2_ponkan'] = $haipai->player2_ponkan;
        $haipai_data['player3_sutehai'] = $haipai->player3_sutehai;
        $haipai_data['player3_nakihai'] = $haipai->player3_nakihai;
        $haipai_data['player3_reach'] = $haipai->player3_reach;
        $haipai_data['player3_ponkan'] = $haipai->player3_ponkan;
        $haipai_data['nokori_hai'] = count($nokori);
        $haipai_data['tsumo_ban'] = $haipai->tsumo_ban;
        

//        if($request['update_time'] != $haipai->update_time){
            $res = ['result'=>'OK','message'=>$haipai_data];
//        }else{
//            $res = ['result'=>'other_time','message'=>"not_change"];
//        }
        $result = json_encode($res);
        return $result;
    }
    public function tenpaicheck($player_hai_data) 
    {
        $phai = $this->seiretu($player_hai_data);
        $hai = explode(',',$phai);
        $hainom = config('const.haiNom');
        $hai_data = array();
        foreach($hai as $val){
            $hai_data[] = $hainom[$val];
        }
        $this->tempTehai = array_count_values($hai_data);

        //前もって完全なシュンツ・コーツ・孤立牌を抜いておく
        $this->kanzen_koutsu_suu = $this->kanzenkoutsucheck();

        $this->kanzen_shuntsu_suu = $this->kanzenshuntsucheck();

        $this->preMentsuCount = $this->kanzen_koutsu_suu + $this->kanzen_shuntsu_suu;
        //5枚目の単騎待ちを阻止する処置
        $kanzen_Koritsu_suu = $this->kanzenkoritsucheck();

        //雀頭抜き出し→コーツ抜き出し→シュンツ抜き出し→ターツ候補抜き出し
        for($i = 0;$i < 27;$i++){
            //頭抜き出し
            if(isset($this->tempTehai[$i])){
                if($this->tempTehai[$i]>=2){
                    $this->toitsu_suu++;
                    $this->tempTehai[$i]-=2;
                    $this->mentu_cut1(1);
                    $this->tempTehai[$i]+=2;
                    $this->toitsu_suu--;
                }
            }
        }
        
        //最終的な結果
        $result = "";
        if($this->syanten_normal == 0){
            $result = "tenpai";
        }
        if($this->syanten_normal == -1){
            $result = "agari";
        }
        return $result;
    }
    public function mentu_cut1($i)
    {
        //※字牌のコーツは完全コーツ処理で抜いているの数牌だけで良い
        for($j = $i;$j < 18;$j++){
            //コーツ抜き出し
            if(isset($this->tempTehai[$j])){
                if($this->tempTehai[$j] >= 3){
                    $this->mentsu_suu++;
                    $this->koutsu_suu++;
                    $this->tempTehai[$j]-=3;
                    $this->mentu_cut1($j);
                    $this->tempTehai[$j]+=3;
                    $this->koutsu_suu--;
                }
            }
            //シュンツ抜き出し
            if(isset($this->tempTehai[$j]) && isset($this->tempTehai[$j+1]) && isset($this->tempTehai[$j+2])){
                if($this->tempTehai[$j] && $this->tempTehai[$j+1] && $this->tempTehai[$j+2] && $j < 16){
                    $this->shuntsu_suu++;
                    $this->tempTehai[$j]--;
                    $this->tempTehai[$j+1]--;
                    $this->tempTehai[$j+2]--;
                    $this->mentu_cut1($j);//自身を呼び出す
                    $this->tempTehai[$j]++;
                    $this->tempTehai[$j+1]++;
                    $this->tempTehai[$j+2]++;
                    $this->shuntsu_suu--;
                }
            }
        }
        $this->taatu_cut(1);//ターツ抜きへ
        return;
    }
    public function taatu_cut($i)
    {
        for($j = $i;$j < 27;$j++){
            $this->mentsu_suu = $this->kanzen_koutsu_suu + $this->koutsu_suu + $this->kanzen_shuntsu_suu + $this->shuntsu_suu;
    
            if($this->mentsu_suu + $this->taatsu_suu < 4){//メンツとターツの合計は4まで
                //トイツ抜き出し
                if(isset($this->tempTehai[$j])){
                    if($this->tempTehai[$j]==2){
                        $this->taatsu_suu++;
                        $this->tempTehai[$j]-=2;
                        $this->taatu_cut($j);
                        $this->tempTehai[$j]+=2;
                        $this->taatsu_suu--;
                    }
                }
                //todo　$j%10 < 9の式が怪しい
                //リャンメン・ペンチャン抜き出し
                if(isset($this->tempTehai[$j]) && isset($this->tempTehai[$j+1])){
                    if($this->tempTehai[$j] && $this->tempTehai[$j+1] && $j < 16 && $j%10 < 9){
                        $this->taatsu_suu++;
                        $this->tempTehai[$j]--;
                        $this->tempTehai[$j+1]--;
                        $this->taatu_cut($j);
                        $this->tempTehai[$j]++;
                        $this->tempTehai[$j+1]++;
                        $this->taatsu_suu--;
                    }
                }
    
                //カンチャン抜き出し
                if(isset($this->tempTehai[$j]) && isset($this->tempTehai[$j+1]) && isset($this->tempTehai[$j+2])){
                    if($this->tempTehai[$j]&&!$this->tempTehai[$j+1] && $this->tempTehai[$j+2] && $j< 16 && $j%10<8){
                        $this->taatsu_suu++;
                        $this->tempTehai[$j]--;
                        $this->tempTehai[$j+2]--;
                        $this->taatu_cut($j);
                        $this->tempTehai[$j]++;
                        $this->tempTehai[$j+2]++;
                        $this->taatsu_suu--;
                    }
                }
            }
        }
    
        $this->syanten_temp=8-$this->mentsu_suu*2-$this->taatsu_suu-$this->toitsu_suu;
        if($this->syanten_temp<$this->syanten_normal) {$this->syanten_normal=$this->syanten_temp;}
        return;
    }
    public function kanzenkoutsucheck()
    {
        $kanzenkoutsu_suu = 0;

        //字牌,萬子の完全コーツを抜き出す
        for($i=18;$i<27;$i++){
            if(isset($this->tempTehai[$i])){
                if($this->tempTehai[$i] >= 3){
                    $this->tempTehai[$i] -= 3;
                    $kanzenkoutsu_suu++;
                }
            }
        }
    
        //数牌の完全コーツを抜き出す
        for($i = 0;$i < 16;$i+=9){
            if(isset($this->tempTehai[$i])){
                if($this->tempTehai[$i] >= 3 && !isset($this->tempTehai[$i+1]) && !isset($this->tempTehai[$i+2])){
                    $this->tempTehai[$i]-=3;
                    $kanzenkoutsu_suu++;
                }
            }
            if(isset($this->tempTehai[$i+1])){
                if(!isset($this->tempTehai[$i]) && $this->tempTehai[$i+1] >= 3 && !isset($this->tempTehai[$i+2]) && !isset($this->tempTehai[$i+4])){
                    $this->tempTehai[$i+1]-=3;
                    $kanzenkoutsu_suu++;
                }
            }
            //3~7の完全コーツを抜く
            for($j = 0;$j < 5;$j++){
                if(isset($this->tempTehai[$i+$j+2])){
                    if(!isset($this->tempTehai[$i+$j]) && !isset($this->tempTehai[$i+$j+1]) && $this->tempTehai[$i+$j+2] >= 3 && !isset($this->tempTehai[$i+$j+4]) && !isset($this->tempTehai[$i+$j+5])){
                        $this->tempTehai[$i+$j+2]-=3;
                        $kanzenkoutsu_suu++;
                    }
                }
            }
            if(isset($this->tempTehai[$i+7])){
                if(!isset($this->tempTehai[$i+5]) && !isset($this->tempTehai[$i+6]) && $this->tempTehai[$i+7] >= 3 && !isset($this->tempTehai[$i+9])){
                    $this->tempTehai[$i+7]-=3;
                    $kanzenkoutsu_suu++;
                }
            }
            if(isset($this->tempTehai[$i+8])){
                if(!isset($this->tempTehai[$i+6]) && !isset($this->tempTehai[$i+7]) && $this->tempTehai[$i+8] >= 3){
                    $this->tempTehai[$i+8]-=3;
                    $kanzenkoutsu_suu++;
                }
            }
        }
        return $kanzenkoutsu_suu;
    }
    public function kanzenshuntsucheck()
    {
        $kanzenshuntsu_suu = 0;
        //123,456のような完全に独立したシュンツを抜き出すための処理
        for($i = 0;$i < 16;$i+=9){
            //ピンズ→ソーズ
            //123▲▲
            if(isset($this->tempTehai[$i]) && isset($this->tempTehai[$i+1]) && isset($this->tempTehai[$i+2])){
                if($this->tempTehai[$i]==2 && $this->tempTehai[$i+1]==2 && $this->tempTehai[$i+2]==2 && !isset($this->tempTehai[$i+3]) && !isset($this->tempTehai[$i+4])){
                    $this->tempTehai[$i]-=2;
                    $this->tempTehai[$i+1]-=2;
                    $this->tempTehai[$i+2]-=2;
                    $kanzenshuntsu_suu+=2;
                }
            }
            //▲234▲▲
            if(isset($this->tempTehai[$i+1]) && isset($this->tempTehai[$i+2]) && isset($this->tempTehai[$i+3])){
                if(!isset($this->tempTehai[$i]) && $this->tempTehai[$i+1]==2 && $this->tempTehai[$i+2]==2 && $this->tempTehai[$i+3]==2 && !isset($this->tempTehai[$i+4]) && !isset($this->tempTehai[$i+5])){
                    $this->tempTehai[$i+1]-=2;
                    $this->tempTehai[$i+2]-=2;
                    $this->tempTehai[$i+3]-=2;
                    $kanzenshuntsu_suu+=2;
                }
            }
            //▲▲345▲▲
            if(isset($this->tempTehai[$i+2]) && isset($this->tempTehai[$i+3]) && isset($this->tempTehai[$i+4])){
                if(isset($this->tempTehai[$i]) && !isset($this->tempTehai[$i+1]) && $this->tempTehai[$i+2]==2 && $this->tempTehai[$i+3]==2 && $this->tempTehai[$i+4]==2 && !isset($this->tempTehai[$i+5]) && !isset($this->tempTehai[$i+6])){
                    $this->tempTehai[$i+2]-=2;
                    $this->tempTehai[$i+3]-=2;
                    $this->tempTehai[$i+4]-=2;
                    $kanzenshuntsu_suu+=2;
                }
            }
            //▲▲456▲▲
            if(isset($this->tempTehai[$i+3]) && isset($this->tempTehai[$i+4]) && isset($this->tempTehai[$i+5])){
                if(!isset($this->tempTehai[$i+1]) && !isset($this->tempTehai[$i+2]) && $this->tempTehai[$i+3]==2 && $this->tempTehai[$i+4]==2 && $this->tempTehai[$i+5]==2 && !isset($this->tempTehai[$i+6]) && !isset($this->tempTehai[$i+7])){
                    $this->tempTehai[$i+3]-=2;
                    $this->tempTehai[$i+4]-=2;
                    $this->tempTehai[$i+5]-=2;
                    $kanzenshuntsu_suu+=2;
                }
            }
            //▲▲567▲▲
            if(isset($this->tempTehai[$i+4]) && isset($this->tempTehai[$i+5]) && isset($this->tempTehai[$i+6])){
                if(!isset($this->tempTehai[$i+2]) && !isset($this->tempTehai[$i+3]) && $this->tempTehai[$i+4]==2 && $this->tempTehai[$i+5]==2 && $this->tempTehai[$i+6]==2 && !isset($this->tempTehai[$i+7]) && !isset($this->tempTehai[$i+8])){
                    $this->tempTehai[$i+4]-=2;
                    $this->tempTehai[$i+5]-=2;
                    $this->tempTehai[$i+6]-=2;
                    $kanzenshuntsu_suu+=2;
                }
            }
            //▲▲678▲
            if(isset($this->tempTehai[$i+5]) && isset($this->tempTehai[$i+6]) && isset($this->tempTehai[$i+7])){
                if(!isset($this->tempTehai[$i+3]) && !isset($this->tempTehai[$i+4]) && $this->tempTehai[$i+5]==2 && $this->tempTehai[$i+6]==2 && $this->tempTehai[$i+7]==2 && !isset($this->tempTehai[$i+8])){
                    $this->tempTehai[$i+5]-=2;
                    $this->tempTehai[$i+6]-=2;
                    $this->tempTehai[$i+7]-=2;
                    $kanzenshuntsu_suu+=2;
                }
            }
            //▲▲789
            if(isset($this->tempTehai[$i+6]) && isset($this->tempTehai[$i+7]) && isset($this->tempTehai[$i+8])){
                if(!isset($this->tempTehai[$i+4]) && !isset($this->tempTehai[$i+5]) && $this->tempTehai[$i+6]==2 && $this->tempTehai[$i+7]==2 && $this->tempTehai[$i+8]==2){
                    $this->tempTehai[$i+6]-=2;
                    $this->tempTehai[$i+7]-=2;
                    $this->tempTehai[$i+8]-=2;
                    $kanzenshuntsu_suu+=2;
                }
            }
        }


        for($i = 0;$i < 16;$i+=9){
            //ピンズ→ソーズ
            //123▲▲
            if(isset($this->tempTehai[$i]) && isset($this->tempTehai[$i+1]) && isset($this->tempTehai[$i+2])){
                if($this->tempTehai[$i]==1 && $this->tempTehai[$i+1]==1 && $this->tempTehai[$i+2]==1 && !isset($this->tempTehai[$i+3]) && !isset($this->tempTehai[$i+4])){
                    $this->tempTehai[$i]--;
                    $this->tempTehai[$i+1]--;
                    $this->tempTehai[$i+2]--;
                    $kanzenshuntsu_suu++;
                }
            }
            //▲234▲▲
            if(isset($this->tempTehai[$i+1]) && isset($this->tempTehai[$i+2]) && isset($this->tempTehai[$i+3])){
                if(!isset($this->tempTehai[$i]) && $this->tempTehai[$i+1]==1 && $this->tempTehai[$i+2]==1 && $this->tempTehai[$i+3]==1 && !isset($this->tempTehai[$i+4]) && !isset($this->tempTehai[$i+5])){
                    $this->tempTehai[$i+1]--;
                    $this->tempTehai[$i+2]--;
                    $this->tempTehai[$i+3]--;
                    $kanzenshuntsu_suu++;
                }
            }
            //▲▲345▲▲
            if(isset($this->tempTehai[$i+2]) && isset($this->tempTehai[$i+3]) && isset($this->tempTehai[$i+4])){
                if(!isset($this->tempTehai[$i]) && !isset($this->tempTehai[$i+1]) && $this->tempTehai[$i+2]==1 && $this->tempTehai[$i+3]==1 && $this->tempTehai[$i+4]==1 && !isset($this->tempTehai[$i+5]) && !isset($this->tempTehai[$i+6])){
                    $this->tempTehai[$i+2]--;
                    $this->tempTehai[$i+3]--;
                    $this->tempTehai[$i+4]--;
                    $kanzenshuntsu_suu++;
                }
            }
            //▲▲456▲▲
            if(isset($this->tempTehai[$i+3]) && isset($this->tempTehai[$i+4]) && isset($this->tempTehai[$i+5])){
                if(!isset($this->tempTehai[$i+1]) && !isset($this->tempTehai[$i+2]) && $this->tempTehai[$i+3]==1 && $this->tempTehai[$i+4]==1 && $this->tempTehai[$i+5]==1 && !isset($this->tempTehai[$i+6]) && !isset($this->tempTehai[$i+7])){
                    $this->tempTehai[$i+3]--;
                    $this->tempTehai[$i+4]--;
                    $this->tempTehai[$i+5]--;
                    $kanzenshuntsu_suu++;
                }
            }
            //▲▲567▲▲
            if(isset($this->tempTehai[$i+4]) && isset($this->tempTehai[$i+5]) && isset($this->tempTehai[$i+6])){
                if(!isset($this->tempTehai[$i+2]) && !isset($this->tempTehai[$i+3]) && $this->tempTehai[$i+4]==1 && $this->tempTehai[$i+5]==1 && $this->tempTehai[$i+6]==1 && !isset($this->tempTehai[$i+7]) && !isset($this->tempTehai[$i+8])){
                    $this->tempTehai[$i+4]--;
                    $this->tempTehai[$i+5]--;
                    $this->tempTehai[$i+6]--;
                    $kanzenshuntsu_suu++;
                }
            }
            //▲▲678▲
            if(isset($this->tempTehai[$i+5]) && isset($this->tempTehai[$i+6]) && isset($this->tempTehai[$i+7])){
                if(!isset($this->tempTehai[$i+3]) && !isset($this->tempTehai[$i+4]) && $this->tempTehai[$i+5]==1 && $this->tempTehai[$i+6]==1 && $this->tempTehai[$i+7]==1 && !isset($this->tempTehai[$i+8])){
                    $this->tempTehai[$i+5]--;
                    $this->tempTehai[$i+6]--;
                    $this->tempTehai[$i+7]--;
                    $kanzenshuntsu_suu++;
                }
            }
            //▲▲789
            if(isset($this->tempTehai[$i+6]) && isset($this->tempTehai[$i+7]) && isset($this->tempTehai[$i+8])){
                if(!isset($this->tempTehai[$i+4]) && !isset($this->tempTehai[$i+5]) && $this->tempTehai[$i+6]==1 && $this->tempTehai[$i+7]==1 && $this->tempTehai[$i+8]==1){
                    $this->tempTehai[$i+6]--;
                    $this->tempTehai[$i+7]--;
                    $this->tempTehai[$i+8]--;
                    $kanzenshuntsu_suu++;
                }
            }
        }
        return $kanzenshuntsu_suu;
    }
    public function kokusicheck($p_hai) 
    {
        $kokusi = 0;
        $mae_hai = "";
        $mae_mae_hai = "";
        $toitu = 0;
        $anko = 0;
        $hai = explode(',',$p_hai);
        sort($hai);
        foreach($hai as $val){
            if($val == "1p"){$kokusi++;}
            if($val == "9p"){$kokusi++;}
            if($val == "1s"){$kokusi++;}
            if($val == "9s"){$kokusi++;}
            if($val == "1z"){$kokusi++;}
            if($val == "2z"){$kokusi++;}
            if($val == "3z"){$kokusi++;}
            if($val == "4z"){$kokusi++;}
            if($val == "5z"){$kokusi++;}
            if($val == "6z"){$kokusi++;}
            if($val == "7z"){$kokusi++;}
            if($val == "1m"){$kokusi++;}
            if($val == "9m"){$kokusi++;}
            if($val == $mae_hai){$toitu++;}
            if($val == $mae_mae_hai){$anko++;}
            $mae_mae_hai = $mae_hai;
            $mae_hai = $val;
        }
        $result = "";
        if(($anko == 0 && $toitu == 1 && $kokusi == 13) || ($anko == 0 && $toitu == 1 && $kokusi == 14) || ($anko == 0 && $toitu == 2 && $kokusi == 13) || ($anko == 0 && $toitu == 2 && $kokusi == 14) || ($anko == 0 && $toitu == 0 && $kokusi == 13)){
            $result = "tenpai";
        }
        if($anko == 0 && $toitu == 1 && $kokusi == 14){
            $result = "agari";
        }
        return $result;
    }
    public function kanzenkoritsucheck() 
    {
        $kanzenkoritsu_suu = 0;
        //萬子,字牌の完全孤立牌を抜き出す
        for($i = 18;$i < 25;$i++){
            if(isset($this->tempTehai[$i])){
                if($this->tempTehai[$i] == 1){
                    //koritsu = i ;//孤立牌を変数に格納する
                    $this->tempTehai[$i]--;
                    $kanzenkoritsu_suu++;
                }
            }
        }

        //数牌の完全孤立牌を抜き出す
        for($i = 0;$i < 16;$i=$i+9){
            //ピンズ→ソーズ
            //1の孤立牌を抜く
            if(isset($this->tempTehai[$i])){
                if($this->tempTehai[$i]==1 && !isset($this->tempTehai[$i+1]) && !isset($this->tempTehai[$i+2])){
                    //koritsu = i+1;//孤立牌を変数に格納する
                    $this->tempTehai[$i]--;
                    $kanzenkoritsu_suu++;
                }
            }
            //2の完全孤立牌を抜く
            if(isset($this->tempTehai[$i+1])){
                if(!isset($this->tempTehai[$i]) && $this->tempTehai[$i+1]==1 && !isset($this->tempTehai[$i+2]) && !isset($this->tempTehai[$i+3])){
                    $this->tempTehai[$i+1]--;
                    $kanzenkoritsu_suu++;
                }
            }
            //3~7の完全孤立牌を抜く
            for($j = 0;$j < 5;$j++){
                if(isset($this->tempTehai[$i+$j+2])){
                    if(!isset($this->tempTehai[$i+$j]) && !isset($this->tempTehai[$i+$j+1]) && $this->tempTehai[$i+$j+2]==1 && !isset($this->tempTehai[$i+$j+3]) && !isset($this->tempTehai[$i+$j+4])){
                        $this->tempTehai[$i+$j+2]--;
                        $kanzenkoritsu_suu++;
                    }
                }
            }
            //8の完全孤立牌を抜く
            if(isset($this->tempTehai[$i+7])){
                if(!isset($this->tempTehai[$i+5]) && !isset($this->tempTehai[$i+6]) && $this->tempTehai[$i+7]==1 && !isset($this->tempTehai[$i+8])){
                    $this->tempTehai[$i+7]--;
                    $kanzenkoritsu_suu++;
                }
            }
            //9の完全孤立牌を抜く
            if(isset($this->tempTehai[$i+8])){
                if(!isset($this->tempTehai[$i+6]) && !isset($this->tempTehai[$i+7]) && $this->tempTehai[$i+8]==1){
                    $this->tempTehai[$i+8]--;
                    $kanzenkoritsu_suu++;
                }
            }
        }

        return $kanzenkoritsu_suu;
    }
    public function titoicheck($player_hai_data) 
    {
        $titoi = 0;
        $mae_hai = "";
        $toitu = 0;
        $hai = explode(',',$player_hai_data);
        sort($hai);
        foreach($hai as $val){
            if($val == $mae_hai){
                $toitu++;
                $mae_hai = "";
            }else{
                $mae_hai = $val;
            }
        }
        $result = "";
        if($toitu == 6){
            $result = "tenpai";
        }
        if($toitu == 7){
            $result = "agari";
        }
        return $result;
    }
    public function reach(Request $request)
    {
        $haipai = DB::table('haipai')->where('game_id',$request->session()->get('game_id'))->first();
        $reach = "reach";
        if($request->session()->get('player_no') == "player1"){
            $p_reach = "player1_reach";
            if($haipai->player1_sutehai == "" && $haipai->player2_nakihai == "" && $haipai->player3_nakihai == ""){
                $reach = "wreach";
            }
        }
        if($request->session()->get('player_no') == "player2"){
            $p_reach = "player2_reach";
            if($haipai->player2_sutehai == "" && $haipai->player1_nakihai == "" && $haipai->player3_nakihai == ""){
                $reach = "wreach";
            }
        }
        if($request->session()->get('player_no') == "player3"){
            $p_reach = "player3_reach";
            if($haipai->player3_sutehai == "" && $haipai->player1_nakihai == "" && $haipai->player2_nakihai == ""){
                $reach = "wreach";
            }
        }
        $result = DB::table('haipai')
        ->where('game_id', $request->session()->get('game_id'))
        ->update([
            $p_reach => $reach,
            'update_time' => Carbon::now()
        ]);

        if($result){
            $res = ['result'=>'OK','message'=>'OK'];
            $result = json_encode($res);
            return $result;
        }else{
            $res = ['result'=>'NG','message'=>'NG'];
            $result = json_encode($res);
            return $result;
        }
    }
    public function tumoagari(Request $request)
    {
        $game_status = DB::table('game_status')->where('id',$request->session()->get('game_id'))->first();
        $haipai = DB::table('haipai')->where('game_id',$request->session()->get('game_id'))->first();
        $tumohai = $request->session()->get('tumohai');
        if($request->session()->get('player_no') == $game_status->oya_ban){
            $oya_ban = "oya";
        }else{
            $oya_ban = "";
        }
        if($request->session()->get('player_no') == "player1"){
            $player_hai = $haipai->player1_hai;
            $player_nakihai = $haipai->player1_nakihai;
            $player_reach = $haipai->player1_reach;
        }
        if($request->session()->get('player_no') == "player2"){
            $player_hai = $haipai->player2_hai;
            $player_nakihai = $haipai->player2_nakihai;
            $player_reach = $haipai->player2_reach;
            
        }
        if($request->session()->get('player_no') == "player3"){
            $player_hai = $haipai->player3_hai;
            $player_nakihai = $haipai->player3_nakihai;
            $player_reach = $haipai->player3_reach;
        }

        if($player_nakihai != ""){
            $p_nakihai = explode(',',$haipai->player_nakihai);
            foreach($p_nakihai as $val){
                if(substr($val, 2, 1) == "p"){
                    $nakihai .= "," . substr($val, 3, 2) . "," . substr($val, 3, 2) . "," . substr($val, 3, 2);
                }
            }
            $p_hai = $player_hai . "," . $tumohai . $nakihai;
        }else{
            $p_hai = $player_hai . "," . $tumohai;
        }
        $tenpai = "";
        if($player_nakihai == ""){
            $tempai = $this->titoicheck($p_hai);
            if($tempai != "agari"){
                $tempai = $this->kokusicheck($p_hai);
            }
            if($tempai != "agari"){
                $tempai = $this->tenpaicheck($p_hai);
            }
        }

        $yakucheck = $this->yakucheck($p_hai,$player_reach,$player_nakihai);
        $tensucheck = $this->tensucheck($yakucheck,$oya_ban,"tumo");
        if(count($tensucheck) !=0){
            $res = ['result'=>'OK','message'=>$tensucheck];
            $result = json_encode($res);
            return $result;
        }else{
            $res = ['result'=>'NG','message'=>'NG'];
            $result = json_encode($res);
            return $result;
        }
    }
    public function yakucheck($p_hai,$p_reach,$p_nakihai)
    {
        $yaku = array();
        $yaku_ck = "";
        if($p_reach == "reach" && $p_nakihai ==""){
            $yaku[] = "reach"; 
            $yaku_ck = $this->titoicheck($p_hai);
            if($yaku_ck == "agari"){
                $yaku[] = "titoi";
            }
            $yaku_ck = "";
            $yaku_ck = $this->kokusicheck($p_hai);
            if($yaku_ck == "agari"){
                $yaku[] = "kokusi";
            }
            $yaku_ck = "";
            $yaku_ck = $this->tanyao($p_hai);
            if($yaku_ck == "tanyao"){
                $yaku[] = "tanyao";
            }
        }
        if($p_nakihai !=""){
            $yaku_ck = "";
            $yaku_ck = $this->tanyao($p_hai);
            if($yaku_ck == "tanyao"){
                $yaku[] = "tanyao";
            }
            $yaku_ck = "";
            $yaku_ck = $this->toitoi($p_hai);
            if($yaku_ck == "toitoi"){
                $yaku[] = "toitoi";
            }
        }
        return $yaku;
    }
    public function tensucheck($yakucheck,$oya_ban,$tumo)
    {
        $result = array();
        if(count($yakucheck) == 0){
            $result['notenpai'] = "-16000";
        }else{
            $yakuPoint = config('const.yakuPoint');
            $hansu = 0;
            foreach($yakucheck as $key=>$val){
                $hansu = $hansu + $yakuPoint[$key];
            }
            if($oya_ban == ""){
                //子
            }else{
                //親
            }
        }
        return $result;
    }
    public function tanyao($p_hai) 
    {
        $not_tanyao = 0;
        $hai = explode(',',$p_hai);
        sort($hai);
        foreach($hai as $val){
            if($val == "1p"){$not_tanyao++;}
            if($val == "9p"){$not_tanyao++;}
            if($val == "1s"){$not_tanyao++;}
            if($val == "9s"){$not_tanyao++;}
            if($val == "1z"){$not_tanyao++;}
            if($val == "2z"){$not_tanyao++;}
            if($val == "3z"){$not_tanyao++;}
            if($val == "4z"){$not_tanyao++;}
            if($val == "5z"){$not_tanyao++;}
            if($val == "6z"){$not_tanyao++;}
            if($val == "7z"){$not_tanyao++;}
            if($val == "1m"){$not_tanyao++;}
            if($val == "9m"){$not_tanyao++;}
        }
        $result = "";
        if($not_tanyao == 0){
            $result = "tanyao";
        }else{
            $result = "";
        }
        return $result;
    }
    public function toitoi($player_hai_data) 
    {
        $phai = $this->seiretu($player_hai_data);
        $hai = explode(',',$phai);
        $hainom = config('const.haiNom');
        $hai_data = array();
        foreach($hai as $val){
            $hai_data[] = $hainom[$val];
        }
        $hai_arr = array_count_values($hai_data);
        $anko_cnt = 0;
        foreach($hai_arr as $val){
            if($val > 2){
                $anko_cnt++;
            }
        }
        if($anko_cnt == 4){
            $result = "toitoi";
        }else{
            $result = "";
        }
        return $result;
    }
}
