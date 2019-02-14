<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;

use Platform\Controllers\Core;

use App\Mail\ResetPassword;
use Illuminate\Support\Facades\Mail;

use Actuallymab\LaravelComment\CanComment;

use App\Scopes\AccountScope;

class User extends Authenticatable implements AttachableInterface
{
    protected $table = 'users';

    use HasRoles;
    use PaperclipTrait;
    use Notifiable;
    use CanComment;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'account_id', 
      'name', 
      'email', 
      'active', 
      'password', 
      'locale', 
      'verification_code', 
      'avatar', 
      'image', 
      'first_name', 
      'last_name', 
      'salutation', 
      'job_title', 
      'date_of_birth', 
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
      'notes', 
      'country_code', 
      'currency_code', 
      'locale', 
      'timezone',  
      'date_format',
      'time_format', 
      'decimals', 
      'decimal_seperator', 
      'thousands_seperator', 
      'first_day_of_week'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'password', 
      'remember_token',
    ];

    /**
     * Field mutators.
     *
     * @var array
     */
    protected $casts = [
      'additional_fields' => 'json',
      'settings' => 'json',
      'tags' => 'json',
      'meta' => 'json'
    ];

    /**
     * Date/time fields that can be used with Carbon.
     *
     * @return array
     */
    public function getDates() {
      return ['created_at', 'updated_at', 'last_login', 'date_of_birth', 'expires'];
    }

    public function __construct(array $attributes = []) {

      /**
       * Paperclip image attachments
       */
      $this->hasAttachedFile('avatar', [
          'variants' => [
              's' => '256x256#',
              'form' => '512x512#',
              'xl' => '800x800#'
          ]
      ]);

      parent::__construct($attributes);
    }

    public static function boot() {
      parent::boot();

      static::addGlobalScope(new AccountScope(auth()->user()));

      // On select
      static::retrieved(function ($model) {
      });

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
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token) {
      // Log
      Core\Log::add(
        'reset_password', 
        trans('g.log_user_reset_password_mail', ['name' => $this->name]),
        '\App\User',
        $this->id,
        $this
      );

      // Send reset password email
      $reset_url = url('password/reset/' . $token . '?email=' . $this->email);
      Mail::to($this->email)->send(new ResetPassword($reset_url, $this->name));
    }

    /**
     * Date / time formatting
     */
    public function formatDate($date, $format = 'date_short') {
			switch ($format) {
				case 'date_medium': $date = $date->timezone($this->getTimezone())->format($this->getUserDateFormat()); break;
				case 'datetime_medium': $date = $date->timezone($this->getTimezone())->format($this->getUserDateFormat() . ' @ ' . $this->getUserTimeFormat()); break;
			}
			return $date;
    }

    /**
     * Check if user was online recently.
     *
     * @return boolean
     */
    public function getRecentlyOnline($minutes = 10) {
      $lastActivity = strtotime(\Carbon\Carbon::now()->subMinutes($minutes));
      $visit = \DB::table('sessions')
        ->whereRaw('user_id = ?', [$this->id])
        ->whereRaw('last_activity >= ?', [$lastActivity])
        ->first();

      return ($visit === null) ? false : true;
    }

    /**
     * Get avatar.
     *
     * @return string for use in <img> src
     */
    public function getAvatar() {
      if ($this->avatar_file_name != NULL) {
        return $this->avatar->url('s');
      } else {
        return \Avatar::create(strtoupper($this->name))->toBase64();
      }
    }

    /**
     * Get avatar html.
     *
     * @return string for use in <img> src
     */
    public function getAvatarHtml() {
      if ($this->avatar_file_name != NULL) {
        $avatar = $this->avatar->url('s');
      } else {
        $avatar = (string) \Avatar::create(strtoupper($this->name))->toBase64();
      }

      $html = '';

      $html .= '<div style="border-radius:50%;display: inline-block;border: 2px solid ' . $this->roles[0]->color . ';">';
      $html .= '<div class="avatar" style="background-image: url(' . $avatar . ');border: 1px solid #fff;" data-placement="top" data-title="';
      $html .= '<div class=\'text-center\'>';
      $html .= '<span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:6px;background-color:' . $this->roles[0]->color . '\'></span>';
      $html .= $this->roles[0]->name;
      $html .= '</div>" data-content="<div class=\'text-center\'><img src=\'' . $avatar . '\' class=\'avatar avatar-xxl\'></div>';
      $html .= '<div class=\'text-center mt-2\'>' . $this->name . '</div>';
      if ($this->job_title !== null) {
        $html .= '<div class=\'text-center text-muted mt-1\'>' . $this->job_title . '</div>';
      }
      $html .= '<div class=\'text-center mt-2\'><i class=\'material-icons\' style=\'font-size:12px; position:relative; top: 2px\'>alternate_email</i> ' . $this->email . '</div>';
      if ($this->phone !== null) {
        $html .= '<div class=\'text-center mt-1\'><i class=\'material-icons\' style=\'font-size:12px; position:relative; top: 1px\'>phone</i> ' . $this->phone . '</div>';
      }
      $html .= '" data-toggle="popover">';
      if ($this->getRecentlyOnline()) {
        $html .= '<span class="avatar-status bg-green"></span>';
      }
      $html .= '</div>';
      $html .= '</div>';

      return $html;
    }

    /**
     * Get name with [inactive] in case a record is not active, and the role name.
     *
     * @return string
     */
    public function getActiveRoleNameAttribute() {
      if ($this->active == 0) {
        return '[' . strtoupper(trans('g.inactive')) . '] ' . $this->roles[0]->name . ' - ' . $this->name;
      } else {
        return $this->roles[0]->name . ' - ' . $this->name;
      }
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
     * User language
     */
    public function getLanguage() {
      if ($this->language === NULL) {
        return config('system.default_language');
      } else {
        return $this->language;
      }
    }

    /**
     * User locale
     */
    public function getLocale() {
      if ($this->locale === NULL) {
        $language = $this->getLanguage();
        // If there is no default for user's language, use global default
        return config('system.language_defaults.' . $language . '.locale', config('system.default_locale'));
      } else {
        return $this->locale;
      }
    }

    /**
     * User timezone
     */
    public function getTimezone() {
      if ($this->timezone === NULL) {
        $language = $this->getLanguage();
        // If there is no default for user's language, use global default
        return config('system.language_defaults.' . $language . '.timezone', config('system.default_timezone'));
      } else {
        return $this->timezone;
      }
    }

    /**
     * User date format
     */
    public function getUserDateFormat() {
      if ($this->date_format === NULL) {
        $language = $this->getLanguage();
        // If there is no default for user's language, use global default
        return config('system.language_defaults.' . $language . '.date_format', config('system.default_date_format'));
      } else {
        return $this->date_format;
      }
    }

    /**
     * User time format
     */
    public function getUserTimeFormat() {
      if ($this->time_format === NULL) {
        $language = $this->getLanguage();
        // If there is no default for user's language, use global default
        return config('system.language_defaults.' . $language . '.time_format', config('system.default_time_format'));
      } else {
        return $this->time_format;
      }
    }

    /**
     * User decimals
     */
    public function getDecimals() {
      if ($this->decimals === NULL) {
        $language = $this->getLanguage();
        // If there is no default for user's language, use global default
        return config('system.language_defaults.' . $language . '.decimals', config('system.default_decimals'));
      } else {
        return $this->decimals;
      }
    }

    /**
     * User decimal seperator
     */
    public function getDecimalSep() {
      if ($this->decimal_seperator === NULL) {
        $language = $this->getLanguage();
        // If there is no default for user's language, use global default
        return config('system.language_defaults.' . $language . '.decimal_seperator', config('system.default_decimal_seperator'));
      } else {
        return $this->decimal_seperator;
      }
    }

    /**
     * User thousands seperator
     */
    public function getThousandsSep() {
      if ($this->thousands_seperator === NULL) {
        $language = $this->getLanguage();
        // If there is no default for user's language, use global default
        return config('system.language_defaults.' . $language . '.thousands_seperator', config('system.default_thousands_seperator'));
      } else {
        return $this->thousands_seperator;
      }
    }

    /**
     * User currency
     */
    public function getCurrency() {
      if ($this->currency_code === NULL) {
        $language = $this->getLanguage();
        // If there is no default for user's language, use global default
        return config('system.language_defaults.' . $language . '.currency', config('system.default_currency'));
      } else {
        return $this->currency_code;
      }
    }

    /**
     * Check if user email has been verified
     *
     * @return bool
     */
    public function isVerified() {
      return ($this->verification_code === null) ? true : false;
    }

    /**
     * Relationships
     * -------------
     */

    public function account() {
      return $this->belongsTo(\App\User::class, 'account_id', 'id');
    }

    public function childUsers() {
      return $this->hasMany(\App\User::class, 'parent_user_id');
    }

    public function logs() {
      return $this->hasMany(\Platform\Models\Log::class);
    }

    public function assignedUsers() {
      return $this->belongsToMany(\App\User::class, 'user_assigned_user', 'assigned_user_id', 'user_id');
    }

    public function companies() {
      return $this->belongsToMany(\Platform\Models\Company::class, 'company_user', 'user_id', 'company_id');
    }

    public function projects() {
      return $this->belongsToMany(\Platform\Models\Project::class, 'project_user', 'user_id', 'project_id');
    }

    public function projectTasks() {
      return $this->hasMany(\Platform\Models\ProjectTask::class);
    }
}
