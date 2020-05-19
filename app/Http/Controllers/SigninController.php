<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class SigninController extends Controller
{
    public function index () 
    {
        $errors = '';
        $users = DB::table('users')->get();
        return view('signin', compact('errors'));
    }
    public function logincheck (Request $request) 
    {
        $users = DB::table('users')->where('name',$request->user_name)->where('password',$request->pass)->count();
        if($users){
            $request->session()->put('user', $request->user_name);
            return redirect('/test');
        }else{
            $errors ="ユーザー名もしくはパスワードが\n間違っています";
        }
        return view('signin', compact('errors'));
    }
}
