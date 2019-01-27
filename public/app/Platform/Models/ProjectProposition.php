<?php
namespace Platform\Models;

use Illuminate\Database\Eloquent\Model;

use Platform\Controllers\Core;

class ProjectProposition extends Model
{
    protected $table = 'project_propositions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'project_id', 
      'project_status_id', 
      'reference',
      'locked',
      'proposition_approved_by_client',
      'description_head', 
      'description_footer', 
      'proposition_valid_until', 
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
      'additional_fields' => 'json',
      'tags' => 'json',
      'meta' => 'json'
    ];

    /**
     * Date/time fields that can be used with Carbon.
     *
     * @return array
     */
    public function getDates() {
      return ['created_at', 'updated_at', 'proposition_valid_until', 'proposition_approved_by_client'];
    }

    public static function boot() {
      parent::boot();

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

    public function project() {
      return $this->belongsTo(\Platform\Models\Project::class);
    }

    public function items() {
      return $this->hasMany(\Platform\Models\ProjectPropositionItem::class, 'project_proposition_id', 'id');
    }

}
