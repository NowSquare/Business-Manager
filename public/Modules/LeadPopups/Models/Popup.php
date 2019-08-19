<?php
namespace Modules\LeadPopups\Models;

use Illuminate\Database\Eloquent\Model;

use Platform\Controllers\Core;

use App\Scopes\AccountScope;

class Popup extends Model
{
    protected $table = 'lead_popups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'name', 
      'active', 
      'additional_fields->submit_button', 
      'additional_fields->after_submit_message', 
      'additional_fields->trigger', 
      'additional_fields->scrollTop', 
      'additional_fields->delay', 
      'additional_fields->ignoreAfterCloses', 
      'additional_fields->position', 
      'additional_fields->width', 
      'additional_fields->height', 
      'additional_fields->shadow', 
      'additional_fields->closeBtnColor', 
      'additional_fields->closeBtnMargin', 
      'additional_fields->backdropBgColor', 
      'additional_fields->backdropVisible', 
      'additional_fields->loaderColor', 
      'additional_fields->showLoader', 
      'content', 
      'form_fields', 
      'hosts', 
      'paths', 
      'referrer_hosts', 
      'referrer_paths', 
      'created_by', 
      'updated_by', 
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Field mutators.
     *
     * @var array
     */
    protected $casts = [
      'active_week_days' => 'json',
      'additional_fields' => 'json',
      'form_fields' => 'json',
      'meta' => 'json'
    ];

    /**
     * Date/time fields that can be used with Carbon.
     *
     * @return array
     */
    public function getDates() {
      return ['created_at', 'updated_at', 'active_start', 'active_end'];
    }

    public static function boot() {
      parent::boot();

      static::addGlobalScope(new AccountScope(auth()->user()));

      // On update
      static::updating(function ($model) {
        if (auth()->check()) {
          $model->updated_by = auth()->user()->id;
        }
      });

      // On create
      self::creating(function ($model) {
        if (auth()->check()) {
          $model->account_id = auth()->user()->account_id;
          $model->created_by = auth()->user()->id;
        }
      });
    }
}
