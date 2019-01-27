<?php
namespace Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;

use Platform\Controllers\Core;

use App\Scopes\AccountScope;

class Company extends Model implements AttachableInterface
{
    use PaperclipTrait;

    protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'account_id',
      'default', 
      'active', 
      'name', 
      'industry', 
      'legal_form', 
      'logo', 
      'lead_source', 
      'phone', 
      'mobile', 
      'website', 
      'fax', 
      'street1', 
      'street2', 
      'city', 
      'state', 
      'postal_code', 
      'country_code', 
      'notes'
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
      'additional_fields' => 'json',
      'meta' => 'json'
    ];

    /**
     * Date/time fields that can be used with Carbon.
     *
     * @return array
     */
    public function getDates() {
      return ['created_at', 'updated_at'];
    }

    public function __construct(array $attributes = []) {

      /**
       * Paperclip image attachments
       */
      $this->hasAttachedFile('logo', [
          'variants' => [
              's' => 'x64',
              'form' => 'x256',
              'xl' => 'x640'
          ]
      ]);

      parent::__construct($attributes);
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
     * Get name with (inactive) in case a record is not active.
     *
     * @return string
     */
    public function getActiveNameAttribute() {
      if ($this->active == 0) {
        return '[' . strtoupper(trans('g.inactive')) . '] ' . $this->name;
      } else {
        return $this->name;
      }
    }

    /**
     * Relationships
     * -------------
     */

    public function account() {
      return $this->belongsTo(\App\User::class, 'id', 'account_id');
    }

    public function users() {
      return $this->belongsToMany(\App\User::class, 'company_user', 'company_id', 'user_id');
    }

    public function projects() {
      return $this->hasMany(\Platform\Models\Project::class);
    }
}
