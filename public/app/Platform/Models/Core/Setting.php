<?php
namespace Platform\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;

use App\Scopes\AccountScope;

Class Setting extends Model implements AttachableInterface
{
  use PaperclipTrait;

  protected $table = 'settings';

  public function __construct(array $attributes = []) {

    /**
     * Paperclip image attachments
     */
    $this->hasAttachedFile('value_image', [
        'variants' => [
            'form' => 'x256',
            'favicon' => '32x32#',
            'icon-s' => '64x64#',
            'icon-l' => '256x256#',
            'landscape_s' => 'x128',
            'portrait_s' => '128',
            'landscape_m' => 'x512',
            'portrait_m' => '512',
            'landscape_l' => 'x1024',
            'portrait_l' => '1024'
        ]
    ]);

    parent::__construct($attributes);
  }

  public static function boot() {
    parent::boot();

    static::addGlobalScope(new AccountScope(auth()->user()));

    // On create
    self::creating(function ($model) {
      if (auth()->check()) {
        $model->account_id = auth()->user()->account_id;
      }
    });
  }

  /**
   * Indicates if the model should be timestamped.
   *
   * @var bool
   */
  public $timestamps = false;

  public function users() {
    return $this->hasOne(App\User::class);
  }
}