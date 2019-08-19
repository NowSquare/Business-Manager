<?php namespace Platform\Models;

use Illuminate\Database\Eloquent\Model;

use App\Scopes\AccountScope;

class Log extends Model {

  protected $table = 'log';

  protected $casts = [
    'meta' => 'json'
  ];

  public $timestamps = false;

  protected $dates = ['created_at'];

  public static function boot() {
    parent::boot();

    static::addGlobalScope(new AccountScope(auth()->user()));

    // On create
    self::creating(function ($model) {
      if (auth()->check()) {
        $model->account_id = auth()->user()->account_id;
      } else {
        $model->account_id = 1;
      }
    });
  }

  public function account() {
    return $this->belongsTo(\App\User::class, 'id', 'account_id');
  }

  public function user() {
    return $this->belongsTo(\App\User::class);
  }
}
