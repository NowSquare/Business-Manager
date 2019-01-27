<?php
namespace Platform\Models;

use Illuminate\Database\Eloquent\Model;

use Platform\Controllers\Core;

class ProjectPropositionItem extends Model
{
    protected $table = 'project_proposition_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'project_proposition_id', 
      'type', 
      'description',
      'quantity',
      'unit',
      'discount_type', 
      'unit_price', 
      'tax_rate', 
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

    public function proposition() {
      return $this->belongsTo(\Platform\Models\ProjectProposition::class);
    }

}
