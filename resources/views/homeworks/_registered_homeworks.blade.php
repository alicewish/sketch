<div class="">
   作业进度：<br>
   @foreach($register_homeworks as $registration)
      @if($registration->thread->id>0)
        @if((Auth::check())&&(Auth::user()->admin))
        <span><a href="{{ route('user.show', $registration->user_id) }}">{{ $registration->student->name }}</a></span>：<span><a href="{{ route('thread.show', $registration->thread->id) }}">{{ $registration->thread->title }}</a></span>：
        @else
        <span>
          @if($registration->thread->anonymous)
          {{$registration->thread->majia ?? '匿名咸鱼'}}
          @else
            {{$registration->student->name}}
          @endif
        </span>：<span><a href="{{ route('thread.show', $registration->thread->id) }}">{{ $registration->thread->title }}</a></span>：
        @endif
        @foreach($registration->thread->posts as $post)
           @if(($post->id>0)&&($post->user_id!=$registration->user_id))
             @if((Auth::check())&&(Auth::user()->admin))
                <span><a href="{{ route('thread.showpost', $post->id) }}">{{ $post->owner->name }}{{'('.$post->up_voted.')'}}</a></span>，
             @else
                <span><a href="{{ route('thread.showpost', $post->id) }}">
                  @if($post->anonymous)
                  {{$post->majia ?? '匿名咸鱼'}}
                  @else
                  {{$post->owner->name}}
                  @endif
                  {{'('.$post->up_voted.')'}}
                </a></span>，
             @endif
           @endif
        @endforeach
        <br>
      @endif
   @endforeach
</div>
