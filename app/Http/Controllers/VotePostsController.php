<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use Auth;
use App\User;
use App\VotePosts;
use Carbon\Carbon;
use App\Activity;

class VotePostsController extends Controller
{
   public function __construct()
   {
      $this->middleware('auth');
   }
   public function findrecord(User $user, Post $post)
   {
      return VotePosts::where('user_id', '=', $user->id)->where('post_id', '=', $post->id)->first();
   }
   public function upvote(Post $post)
   {
      $candidate = User::findOrFail($post->user_id);
      $record = Auth::user()->findrecord($post->id);
      if (!$record){
         $record = VotePosts::create([
           'user_id' => Auth::id(),
           'post_id' => $post->id,
           'upvoted' => true,
           'upvoted_at' => Carbon::now(),
         ]);
         $post->increment('up_voted');

         if($candidate){
            $candidate->increment('upvoted');
            if(!$candidate->no_upvote_reminders){//假如这位被点赞用户设定接收点赞提醒
              $candidate->increment('upvote_reminders');
            }
            if($candidate->upvoted % 20 == 19){
               $candidate->jifen +=5;
               $candidate->xianyu +=1;
               $candidate->shengfan +=5;
            }
            $upvote_acticity = Activity::create([
               'type' => 5,
               'item_id' => $record->id,
               'user_id' => $candidate->id,
            ]);
         }
      }else{
         if ($record->downvoted){//首先，不能已经踩过
            return "notwork";
         }else{
            if ($record->upvoted){//已经赞过的，先取消赞
               $activity = Activity::where('type', '=', 5)->where('item_id','=',$record->id)->first();
               $record->update(['upvoted' => false]);
               $post->decrement('up_voted');
               if($candidate){
                  $candidate->decrement('upvoted');
                  if(!$activity->seen){
                    $candidate->decrement('upvote_reminders');
                  }
               }
               $activity->delete();
            }else{//没有赞过的，赞
               $record->update([
                 'upvoted' => true,
                 'upvoted_at' => Carbon::now(),
               ]);
               $post->increment('up_voted');
               if($candidate){
                  $candidate->increment('upvoted');
                  if(!$candidate->no_upvote_reminders){//假如这位被点赞用户设定接收点赞提醒
                    $candidate->increment('upvote_reminders');
                  }
                  if($candidate->upvoted % 20 == 19){
                     $candidate->jifen +=5;
                     $candidate->xianyu +=1;
                     $candidate->shengfan +=5;
                  }
                  $activity = Activity::where('type', '=', 5)->where('item_id','=',$record->id)->first();
                  if (!$activity){
                    $upvote_acticity = Activity::create([
                       'type' => 5,
                       'item_id' => $record->id,
                       'user_id' => $candidate->id,
                    ]);
                  }
               }
            }
         }
      }
      return $post->up_voted;
   }
   public function downvote(Post $post)
   {
      $candidate = User::findOrFail($post->user_id);
      $record = Auth::user()->findrecord($post->id);
      if (!$record){
         $record = VotePosts::create([
           'user_id' => Auth::id(),
           'post_id' => $post->id,
           'downvoted' => true,
           'downvoted_at' => Carbon::now()
         ]);
         if($candidate){
            $candidate->increment('downvoted');
         }
         $post->increment('down_voted');
      }else{
         if ($record->upvoted){//首先，不能已经赞过
            return "notwork";
         }else{
            if ($record->downvoted){//已经踩过的，先取消踩
               $record->update(['downvoted' => false]);
               $post->decrement('down_voted');
               if($candidate){
                  $candidate->decrement('downvoted');
               }
            }else{//没有踩过的，踩
               $record->update([
                 'downvoted' => true,
                 'downvoted_at' => Carbon::now(),
               ]);
               $post->increment('down_voted');
               if($candidate){
                  $candidate->increment('downvoted');
               }
            }
         }
      }
      return $post->down_voted;
   }
   public function funny(Post $post)
   {
      $record = Auth::user()->findrecord($post->id);
      if (!$record){
         $record = VotePosts::create([
           'user_id' => Auth::id(),
           'post_id' => $post->id,
           'funny' => true,
           'funny_at' => Carbon::now(),
         ]);
         $post->increment('funny');
      }else{
         if ($record->funny){//已经觉得好笑的，先取消好笑
            $record->update(['funny' => false]);
            $post->decrement('funny');
         }else{//不觉得好笑的，觉得好笑
            $record->update([
              'funny' => true,
              'funny_at' => Carbon::now(),
            ]);
            $post->increment('funny');
         }
      }
      return $post->funny;
   }
   public function fold(Post $post)
   {
      $record = Auth::user()->findrecord($post->id);
      if (!$record){
         $record = VotePosts::create([
           'user_id' => Auth::id(),
           'post_id' => $post->id,
           'better_to_fold' => true,
           'better_to_fold_at' => Carbon::now(),
         ]);
         $post->increment('fold');
      }else{
         if ($record->better_to_fold){
            $record->update(['better_to_fold' => false]);
            $post->decrement('fold');
         }else{
            $record->update([
              'better_to_fold' => true,
              'better_to_fold_at' => Carbon::now(),
            ]);
            $post->increment('fold');
         }
      }
      return $post->fold;
   }
   public function index()//havenot finished 选择最新得到赞的回帖
   {
     $statuses = DB::table('vote_posts')
        ->join('users','vote_posts.user_id','=','users.id')
        ->join('posts','vote_posts.post_id','=','posts.id')
        ->where('users.deleted_at', '=', null)
        ->where('users.deleted_at', '=', null)
        ->where('posts.deleted_at', '=', null)
        ->where('posts.fold_state', '=', false)
        ->select('posts.*','users.name','vote_posts.upvoted_at')
        ->orderBy('vote_posts.upvoted_at','desc')
        ->paginate(Config::get('constants.index_per_page'));
     $collections = false;
     return view('statuses.index', compact('statuses','collections'));
   }
}
