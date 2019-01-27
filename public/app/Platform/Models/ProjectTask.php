<?php
namespace Platform\Models;

use Illuminate\Database\Eloquent\Model;

use Platform\Controllers\Core;

class ProjectTask extends Model
{
    protected $table = 'project_tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'parent_id', 
      'project_id',
      'project_status_id',
      'assigned_to_id',
      'for_verification_id',
      'reference',
      'public',
      'billable',
      'optional',
      'subject',
      'priority',
      'progress',
      'recurring',
      'repeat_every',
      'description',
      'start_date',
      'due_date',
      'completed_date',
      'completed_by_id',
      'hourly_rate',
      'hours',
      'estimated_hours',
      'actual_hours',
      'additional_fields',
      'tags',
      'meta',
      'created_by',
      'updated_by'
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
      return ['created_at', 'updated_at', 'completed_date', 'start_date', 'due_date'];
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
          $model->created_by = auth()->user()->id;
        }
      });
    }

    /**
     * Relationships
     * -------------
     */

    public function assignee() {
      return $this->hasOne(\App\User::class, 'id', 'assigned_to_id');
    }

    public function assignees() {
      return $this->belongsToMany(\App\User::class, 'project_task_user', 'project_task_id', 'user_id');
    }

    public function project() {
      return $this->belongsTo(\Platform\Models\Project::class, 'project_id', 'id');
    }

    public function status() {
      return $this->hasOne(\Platform\Models\ProjectStatus::class, 'id', 'project_status_id');
    }

}
