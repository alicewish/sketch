@extends('layouts.default')
@section('title', '写新文章')
@section('content')
   <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
     <div class="panel panel-default">
       <div class="panel-heading">
         <h4>写新文章</h4>
       </div>
       <div class="panel-body">
         @include('shared.errors')

         <form method="POST" action="{{ route('book.store') }}" name="create_book">
           {{ csrf_field() }}
               <div>
                 <h6>（发文前请阅读：<a href="http://sosad.fun/threads/136">版规的详细说明（草案）</a>。关于网站使用的常规问题，可以查看如下页面：<a href="{{ route('about') }}">关于本站</a>，<a href="{{ route('help') }}">使用帮助</a>。感谢发文！）</h6>
                 <h4>1. 请选择文章原创性</h4>
                 <label class="radio-inline"><input type="radio" name="originalornot" value="1" {{ old('originalornot')=='1' ? 'checked' : '' }} onclick="document.getElementById('yuanchuang').style.display = 'block'; document.getElementById('tongren').style.display = 'none'">原创</label>
                 <label class="radio-inline"><input type="radio" name="originalornot" value="0" {{ old('originalornot')=='0' ? 'checked' : '' }} onclick="document.getElementById('tongren').style.display = 'block'; document.getElementById('yuanchuang').style.display = 'none';">同人</label>
               </div>
               @include('books._book_input')
           <button type="submit" class="btn btn-danger sosad-button">发布</button>
         </form>
       </div>
     </div>
   </div>
@stop
