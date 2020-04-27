<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class TestController extends Controller
{
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

        $haipai_nokorihai = explode(',',$haipai->nokori_hai);

        $nokorihai = array_shift($haipai_nokorihai);
        $haipai_data = "";
        foreach($haipai_nokorihai as $val){
            $haipai_data .= $val .",";
        }
        $nokori_data = substr($haipai_data, 0, -1);
        $tumohai = substr($haipai->nokori_hai,0,2);
        Log::debug($tumohai);
        $result = DB::table('haipai')
        ->where('game_id', $request->session()->get('game_id'))
        ->update([
            'nokori_hai'=> $nokori_data,
            'tsumo_ban'=> $request->session()->get('player_no') . "_tumo"
        ]);
        
        if($game_status->status == 1){
            $res = ['result'=>'OK','message'=>$tumohai];
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
            if($haipai->player1_sutehai !=""){
                $sutehai_data = $haipai->player1_sutehai . "," . $request['sutehai'];
            }else{
                $sutehai_data = $request['sutehai'];
            }
            $player_hai = explode(',',$haipai->player1_hai);
        }
        if($request->session()->get('player_no') == "player2"){
            if($haipai->player2_sutehai !=""){
                $sutehai_data = $haipai->player2_sutehai . "," . $request['sutehai'];
            }else{
                $sutehai_data = $request['sutehai'];
            }
            $player_hai = explode(',',$haipai->player2_hai);
        }
        if($request->session()->get('player_no') == "player3"){
            if($haipai->player3_sutehai !=""){
                $sutehai_data = $haipai->player3_sutehai . "," . $request['sutehai'];
            }else{
                $sutehai_data = $request['sutehai'];
            }
            $player_hai = explode(',',$haipai->player3_hai);
        }
        if($request['tumohai'] != ""){
            $dupe = "";
            $hai_data = "";
            foreach($player_hai as $val){
                if($val == $request['sutehai'] && $dupe == ""){
                    $dupe = "dupe";
                    $hai_data .= $request['tumohai'] . ",";
                }else{
                    $hai_data .= $val . ",";
                }
            }
            $p_hai_data = substr($hai_data, 0, -1);
            $player_hai = ltrim($p_hai_data, ',');
            $p_hai = $this->seiretu($player_hai);
        }else{
            $p_hai = $haipai->player1_hai;
        }
        if($request->session()->get('player_no') == "player1"){
            $result = DB::table('haipai')
            ->where('game_id', $request->session()->get('game_id'))
            ->update([
                'player1_hai'=>$p_hai,
                'player1_sutehai'=>$sutehai_data,
                'tsumo_ban'=> "player2"
            ]);
        }
        if($request->session()->get('player_no') == "player2"){
            $result = DB::table('haipai')
            ->where('game_id', $request->session()->get('game_id'))
            ->update([
                'player2_hai'=>$p_hai,
                'player2_sutehai'=>$sutehai_data,
                'tsumo_ban'=> "player3"
            ]);
        }
        if($request->session()->get('player_no') == "player3"){
            $result = DB::table('haipai')
            ->where('game_id', $request->session()->get('game_id'))
            ->update([
                'player3_hai'=>$p_hai,
                'player3_sutehai'=>$sutehai_data,
                'tsumo_ban'=> "player1"
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
        $haipai_data['player1_ponkan'] = $haipai->player1_ponkan;
        $haipai_data['player2_sutehai'] = $haipai->player2_sutehai;
        $haipai_data['player2_nakihai'] = $haipai->player2_nakihai;
        $haipai_data['player2_ponkan'] = $haipai->player2_ponkan;
        $haipai_data['player3_sutehai'] = $haipai->player3_sutehai;
        $haipai_data['player3_nakihai'] = $haipai->player3_nakihai;
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
    private function haipai ($game_id){
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
        
        DB::table('haipai')->insert([
            'game_id' => $game_id,
            'haipai' => $haipai,
            'player1_hai' => $player1_hai,
            'player1_sutehai' => "",
            'player1_nakihai' => "",
            'player1_ponkan' => "",
            'player2_hai' => $player2_hai,
            'player2_sutehai' => "",
            'player2_nakihai' => "",
            'player2_ponkan' => "",
            'player3_hai' => $player3_hai,
            'player3_sutehai' => "",
            'player3_nakihai' => "",
            'player3_ponkan' => "",
            'dorayama_hai' => $dora_yama,
            'nokori_hai' => $nokori_hai,
            'tsumo_ban' =>"player1"
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
    public function seiretu ($player_hai_data) 
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
    public function gamecheck (Request $request) 
    {
        $game_status = DB::table('game_status')->where('id',$request->session()->get('game_id'))->first();
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
        $haipai_data['player1_ponkan'] = $haipai->player1_ponkan;
        $haipai_data['player2_sutehai'] = $haipai->player2_sutehai;
        $haipai_data['player2_nakihai'] = $haipai->player2_nakihai;
        $haipai_data['player2_ponkan'] = $haipai->player2_ponkan;
        $haipai_data['player3_sutehai'] = $haipai->player3_sutehai;
        $haipai_data['player3_nakihai'] = $haipai->player3_nakihai;
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
}
