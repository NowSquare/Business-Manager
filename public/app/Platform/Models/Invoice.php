<?php
namespace Platform\Models;

use Illuminate\Database\Eloquent\Model;

use Platform\Controllers\Core;

class Invoice extends Model
{
    protected $table = 'invoices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'company_id', 
      'project_id', 
      'reference',
      'locked',
      'notes', 
      'description_head', 
      'description_footer', 
      'issue_date',
      'due_date',
      'currency_code',
      'sent_to_email',
      'sent_date',
      'resent_date',
      'partially_paid_date',
      'paid_date',
      'written_off_date',
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
      return ['created_at', 'updated_at', 'issue_date', 'due_date', 'sent_date', 'resent_date', 'partially_paid_date', 'paid_date', 'written_off_date'];
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
     * Get status as translated name.
     *
     * @return string
     */
    public function getStatusAttribute() {
      return trans('g.invoice_statuses.' . $this->status_key . '.label');
    }

    /**
     * Get status key.
     *
     * @return string
     */
    public function getStatusKeyAttribute() {
      if ($this->sent_date === null) {
        return 'draft';
      } elseif ($this->paid_date === null && $this->partially_paid_date === null && $this->written_off_date === null) {
        if ($this->due_date->isPast()) {
          return 'overdue';
        } else {
          return 'sent';
        }
      } else {
        if ($this->written_off_date !== null) {
          return 'written_off';
        } elseif ($this->partially_paid_date !== null && $this->paid_date === null) {
          return 'partially_paid';
        } else {
          return 'paid';
        }
      }
    }

    /**
     * Get status color.
     *
     * @return string
     */
    public function getStatusColorAttribute() {
      return trans('g.invoice_statuses.' . $this->status_key . '.color');
    }

    /**
     * Relationships
     * -------------
     */

    public function client() {
      return $this->belongsTo(\Platform\Models\Company::class, 'company_id', 'id');
    }

    public function project() {
      return $this->belongsTo(\Platform\Models\Project::class);
    }

    public function items() {
      return $this->hasMany(\Platform\Models\InvoiceItem::class, 'invoice_id', 'id');
    }

}
