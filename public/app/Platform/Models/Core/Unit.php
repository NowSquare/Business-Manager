<?php
namespace Platform\Models\Core;

use Illuminate\Database\Eloquent\Model;

use App\Scopes\AccountScope;

class Unit extends Model
{

  protected $table = 'units';

  public static function boot() {
    parent::boot();

    static::addGlobalScope(new AccountScope(auth()->user()));

    // On create
    self::creating(function ($model) {
      if (auth()->check()) {
        $model->account_id = auth()->user()->account_id;
      }
    });
  }

  /**
   * Get default unit name.
   *
   * @return string
   */
  static public function getDefault() {
    return (Unit::where('default', 1)->first() !== null) ? Unit::where('default', 1)->first()->name : '';
  }

  /**
   * Indicates if the model should be timestamped.
   *
   * @var bool
   */
  public $timestamps = false;
}