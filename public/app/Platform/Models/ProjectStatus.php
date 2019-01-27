<?php
namespace Platform\Models;

use Illuminate\Database\Eloquent\Model;

use Platform\Controllers\Core;

class ProjectStatus extends Model
{
    protected $table = 'project_statuses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'active', 
      'name', 
      'color', 
      'description'
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
    protected $casts = [];

    /**
     * Get name with color bullet.
     *
     * @return string
     */
    public function getBulletNameAttribute() {
        return '<span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:' . $this->color . '\'></span>' . $this->name;
    }

    /**
     * Get default project value.
     *
     * @return id
     */
    static public function getDefaultProject() {
      $default_project = ProjectStatus::where('default_project', 1)->first();
      return ($default_project !== null) ? $default_project->id : '';
    }

    /**
     * Get default task value.
     *
     * @return id
     */
    static public function getDefaultTask() {
      $default_task = ProjectStatus::where('default_task', 1)->first();
      return ($default_task !== null) ? $default_task->id : '';
    }

    /**
     * Relationships
     * -------------
     */

    public function project() {
      return $this->hasMany(\Platform\Models\Project::class, 'project_status_id', 'id');
    }
}
