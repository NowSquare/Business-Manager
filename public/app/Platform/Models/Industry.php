<?php namespace Platform\Models;

use Illuminate\Database\Eloquent\Model;

use App\Scopes\AccountScope;

class Industry extends Model {

  protected $table = 'industries';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'account_id', 
    'name'
  ];

  public $timestamps = false;

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
   * Get JSON translation if exists (resources/lang/[language].json
   *
   * @return string
   */
  public function getNameTranslatedAttribute() {
    return __($this->name);
  }

}
