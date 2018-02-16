@if((!$thread->noreply)&&(!$thread->locked)&&(($thread->public)||($thread->user_id==Auth::user()->id)))
<div class="panel-group">
   <form id="replyToThread" action="{{ route('post.store', $thread) }}" method="POST">
      {{ csrf_field() }}
      <div class="hidden" id="reply_to_post">
         <span class="" id="reply_to_post_info"></span>
         <button type="button" class="label"><span class="glyphicon glyphicon glyphicon-remove" onclick="cancelreplytopost()"></span></button>
      </div>
      <input type="hidden" name="reply_to_post" id="reply_to_post_id" class="form-control" value="0"></input>
      <input type="hidden" name="default_chapter_id" id="default_chapter_id" value="{{ $defaultchapter }}"></input>
      <div class="form-group">
         <textarea name="body" rows="7" class="form-control" id="markdowneditor" placeholder="评论十个字起哦～" value="{{ old('body') }}"></textarea>
         <button type="button" onclick="retrievecache('markdowneditor')" class="sosad-button-control addon-button">恢复数据</button>
         <button href="#" type="button" onclick="wordscount('markdowneditor');return false;" class="pull-right sosad-button-control addon-button">字数统计</button>
      </div>
      <div class="checkbox">
        <label><input type="checkbox" name="anonymous" onclick="document.getElementById('majiareplythread{{$thread->id}}').style.display = 'block'">马甲？</label>&nbsp;
        <label><input type="checkbox" name="markdown" onclick="$('#markdowneditor').markdown()">Markdown语法？</label>
        <div class="form-group text-right" id="majiareplythread{{$thread->id}}" style="display:none">
            <input type="text" name="majia" class="form-control" value="{{Auth::user()->majia ?:'匿名咸鱼'}}" placeholder="请输入不超过10字的马甲">
            <label for="majia"><small>(马甲仅勾选“匿名”时有效)</small></label>
        </div>
      </div>
      <button type="submit" name="store_button" value="Store" class="btn btn-danger sosad-button">回复</button>
      @if((Auth::id()==$thread->creator->id)&&($thread->book_id!=0))
         <a href="{{ route('book.createchapter', $thread->book_id) }}" class="btn btn-warning sosad-button">去新页面更新</a>
      @endif
   </form>
</div>
@else
<div class="text-center">
   本帖锁定或由于作者设置，不能跟帖
</div>
@endif