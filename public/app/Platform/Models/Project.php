<?php
namespace Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;

use Platform\Controllers\Core;

use Actuallymab\LaravelComment\Contracts\Commentable;
use Actuallymab\LaravelComment\HasComments;

class Project extends Model implements AttachableInterface, Commentable
{
    use PaperclipTrait;
    use HasComments;

    protected $table = 'projects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'parent_id', 
      'company_id', 
      'project_status_id', 
      'reference',
      'files_dir',
      'active', 
      'name', 
      'category', 
      'short_description', 
      'description',
      'notes',
      'progress',
      'estimated_hours',
      'actual_hours',
      'actual_hours',
      'estimated_costs', 
      'actual_costs', 
      'estimate_valid_until',
      'start_date',
      'due_date',
      'completed_date',
      'completed_by_id',
      'client_can_comment',
      'client_can_view_tasks',
      'client_can_edit_tasks',
      'client_can_view_description',
      'client_can_upload_files',
      'client_can_view_proposition',
      'client_can_approve_proposition',
      'notify_people_involved',
      'notes',
      'currency_code',
      'locale',
      'timezone',
      'date_format',
      'time_format',
      'number_format',
      'decimals',
      'decimal_seperator',
      'thousands_seperator',
      'minus_sign',
      'use_parentheses_for_negative_numbers',
      'first_day_of_week',
      'additional_fields',
      'tags',
      'meta',
      'image'
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
      return ['created_at', 'updated_at', 'deadline', 'start_date', 'due_date', 'estimate_valid_until'];
    }

    public function __construct(array $attributes = []) {

      /**
       * Paperclip image attachments
       */
      $this->hasAttachedFile('image', [
          'variants' => [
              's' => 'x64',
              'form' => 'x256',
              'xl' => 'x1080'
          ]
      ]);

      parent::__construct($attributes);
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
     * Get name with [inactive] in case a record is not active.
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
     * Get task progress.
     *
     * @return number
     */
    public function getTaskProgressAttribute() {
      $total_tasks = $this->tasks->count();
      $completed_tasks = $this->tasks()->whereNotNull('completed_date')->count();
      $task_progress = ($completed_tasks == 0) ? 0 : ($completed_tasks / $total_tasks);

      return ($total_tasks == 0) ? -1 : $task_progress;
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

    public function managers() {
      return $this->belongsToMany(\App\User::class, 'project_manager', 'project_id', 'user_id');
    }

    public function client() {
      return $this->belongsTo(\Platform\Models\Company::class, 'company_id', 'id');
    }

    public function status() {
      return $this->hasOne(\Platform\Models\ProjectStatus::class, 'id', 'project_status_id');
    }

    public function tasks() {
      return $this->hasMany(\Platform\Models\ProjectTask::class, 'project_id', 'id');
    }

    public function propositions() {
      return $this->hasMany(\Platform\Models\ProjectProposition::class, 'project_id', 'id');
    }

}
