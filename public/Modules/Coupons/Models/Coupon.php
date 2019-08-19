<?php
namespace Modules\Coupons\Models;

use Illuminate\Database\Eloquent\Model;

use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;

use Platform\Controllers\Core;

use App\Scopes\AccountScope;

class Coupon extends Model implements AttachableInterface {
    use PaperclipTrait;

    protected $table = 'coupons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'name',
      'slug',
      'active',
      'content',
      'redemption_code',
      'image',
      'favicon',
      'location',
      'expiration_date',
      'phone',
      'website',
      'form_fields',
      'additional_fields->primary_bg_color',
      'additional_fields->primary_text_color',
      'additional_fields->secondary_bg_color',
      'additional_fields->secondary_text_color',
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
      'form_fields' => 'json',
      'additional_fields' => 'json',
      'settings' => 'json',
      'meta' => 'json'
    ];

    /**
     * Date/time fields that can be used with Carbon.
     *
     * @return array
     */
    public function getDates() {
      return ['created_at', 'updated_at', 'valid_from_date', 'expiration_date', 'last_redemption'];
    }

    public function __construct(array $attributes = array()) {

        $this->hasAttachedFile('image', [
            'variants' => [
                'favicon' => '32x32#',
                'small-portrait' => 'x320',
                'small-landscape' => '320',
                'preview' => '800',
                'large' => '1920'
            ]
        ]);

        $this->hasAttachedFile('favicon', [
            'variants' => [
                '16' => '16x16#',
                '32' => '32x32#',
                '96' => '96x96#',
                'iphone_ios7' => '120x120#',
                'ipad_ios7' => '152x152#',
                'iphone_ios8' => '180x180#',
                'ipad_ios8' => '167x167#'
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

    public function getUrl() {
      $url = url('coupon/' . $this->slug);
      return $url;
    }

    public function getFavicon() {
      $favicon = 'favicons/coupon-' . \App\Http\Controllers\Core\Secure::staticHash($this->id) . '.ico';
      $favicon = (\File::exists(public_path($favicon))) ? url($favicon) : null;

      return $favicon;
    }
}
