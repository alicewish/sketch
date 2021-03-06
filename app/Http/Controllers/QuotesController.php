<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Quote;
use Auth;

class QuotesController extends Controller
{
   public function __construct()
    {
      $this->middleware('auth');
    }
   public function store(Request $request)
  {
     $this->validate($request, [
         'quote' => 'required|string|max:80',
         'notsad' => 'required',
      ]);
      if (request('anonymous')){
         $this->validate($request, [
             'majia' => 'required|string|max:10',
          ]);
      }
      $newquote = new Quote;
      $newquote->quote = request('quote');
      $newquote->user_id = auth()->id();
      if (request('anonymous')){
         $newquote->anonymous = true;
         $newquote->majia = request('majia');
      }
      if (request('notsad'==1)){
         $newquote->notsad = true;
      }
      $newquote->save();

     return redirect()->route('home');
  }
  public function create(){
     return view('quotes.create');
  }
  public function edit(){

  }
  public function update(){

  }
  public function xianyu(Quote $quote, Request $request){
     $user = Auth::user();
     if ($user->xianyu<=0){
        return back()->with("info", "抱歉，您的咸鱼不足");
     }
     $quote->increment('xianyu');
     $user->decrement('xianyu');
     $user->increment('jifen', 2);
     $quote->creator->increment('xianyu');
     $quote->creator->increment('jifen',2);
     return back()->with("success", "您已成功投掷咸鱼~");
  }
}
