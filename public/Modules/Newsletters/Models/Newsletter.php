<?php
namespace Modules\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;

use Platform\Controllers\Core;

use App\Scopes\AccountScope;

class Newsletter extends Model
{
    protected $table = 'newsletters';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'name',
      'subject',
      'from_name',
      'from_email',
      'content',
      'style',
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
      'settings' => 'json',
      'meta' => 'json'
    ];

    /**
     * Date/time fields that can be used with Carbon.
     *
     * @return array
     */
    public function getDates() {
      return ['created_at', 'updated_at', 'last_sent_date'];
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

    /**
     * Relationships
     * -------------
     */

    public function createdBy() {
      return $this->belongsTo(\App\User::class, 'created_by', 'id');
    }

    public function updatedBy() {
      return $this->belongsTo(\App\User::class, 'updated_by', 'id');
    }

    public function users() {
      return $this->belongsToMany(\App\User::class, 'newsletter_user', 'newsletter_id', 'user_id');
    }

    public function roles() {
      return $this->belongsToMany(\App\Role::class, 'newsletter_role', 'newsletter_id', 'role_id');
    }

    public function sources() {
      return $this->belongsToMany(\App\User::class, 'newsletter_lead_source', 'newsletter_id', 'lead_source');
    }
}
