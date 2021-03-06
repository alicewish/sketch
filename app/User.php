<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\DB;

use App\Thread;
use App\Linkaccount;

class User extends Authenticatable
{
     use Notifiable;
     use SoftDeletes;
     protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'lastresponded_at', 'introduction', 'invitation_token', 'majia'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'email', 'remember_token','invitation_token',
    ];

    public static function boot()
    {
      parent::boot();
      static::creating(function ($user) {
           $user->activation_token = str_random(30);
      });
   }
   /**
    * Send the password reset notification.
    *
    * @param  string  $token
    * @return void
    */
    //overriding existing sendpassword reset notification
   public function sendPasswordResetNotification($token)
   {
       $this->notify(new ResetPasswordNotification($token));
   }

   public function threads()
   {
      return $this->hasMany(Thread::class);
   }

   public function statuses()
   {
      return $this->hasMany(Status::class);
   }

   public function isAdvanced($usergroup)
    {
      return ($this->group >= usergroup);
    }

   public function collected_books()
   {
      return $this->belongsToMany(Thread::class, 'collections', 'user_id', 'thread_id')->where('book_id', '>', 0);
   }

   public function collected_threads()
   {
     return $this->belongsToMany(Thread::class, 'collections', 'user_id', 'thread_id')->where('book_id', '=', 0);
  }
  public function findrecord($post_id)
  {
     return VotePosts::where('user_id', '=', $this->id)->where('post_id', '=', $post_id)->first();
  }
  public function upvotedpost($post_id)
  {
     $record = $this->findrecord($post_id);
     return (($record) && ($record->upvoted));
  }
  public function downvotedpost($post_id)
  {
     $record = $this->findrecord($post_id);
     return (($record) && ($record->downvoted));
  }
  public function funnypost($post_id)
  {
     $record = $this->findrecord($post_id);
     return (($record) && ($record->funny));
  }
  public function foldpost($post_id)
  {
     $record = $this->findrecord($post_id);
     return (($record) && ($record->better_to_fold));
  }

  public function feed()
    {
      $user_ids = Auth::user()->followings->pluck('id')->toArray();
      array_push($user_ids, Auth::user()->id);
      return Status::whereIn('user_id', $user_ids)
                              ->with('user')
                              ->orderBy('created_at', 'desc');
    }

    public function followers()
    {
      return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    public function followings()
    {
      return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    public function follow($user_ids)
    {
      if (!is_array($user_ids)){
        $user_ids = compact('user_ids');
      }
      $this->followings()->sync($user_ids, false);
    }
    public function unfollow($user_ids)
    {
      if (!is_array($user_ids)){
        $user_ids = compact('user_ids');
      }
      $this->followings()->detach($user_ids);
    }

    public function isFollowing($user_id)
    {
      return $this->followings->contains($user_id);
    }
    public function checklevelup()
    {
      $level_ups = Config::get('constants.level_up');
      foreach($level_ups as $level=>$requirement){
         if (($this->user_level < $level)
         &&(!(array_key_exists('continued_qiandao',$requirement))||($requirement['continued_qiandao']<=$this->continued_qiandao))
         &&(!(array_key_exists('jifen',$requirement))||($requirement['jifen']<=$this->jifen))
         &&(!(array_key_exists('xianyu',$requirement))||($requirement['xianyu']<=$this->xianyu))
         &&(!(array_key_exists('sangdian',$requirement))||($requirement['sangdian']<=$this->sangdian))){
            $this->user_level = $level;
            $this->save();
            return true;
         }
      }
      return false;
    }
   public function linked($id){
      $link1 = Linkaccount::where([['account1','=',$id],['account2','=',$this->id]])->first();
      $link2 = Linkaccount::where([['account2','=',$id],['account1','=',$this->id]])->first();
      return ($link1||$link2);
   }
   public function linkedaccounts()
   {
      $firstgroup = DB::table('linkaccounts')
         ->where('account1','=',$this->id)
         ->join('users','linkaccounts.account2','=','users.id')
         ->select('users.id','users.name');
      $secondgroup = DB::table('linkaccounts')
         ->where('account2','=',$this->id)
         ->join('users','linkaccounts.account1','=','users.id')
         ->select('users.id','users.name')
         ->union($firstgroup)
         ->get();
      return $secondgroup;
   }
}
