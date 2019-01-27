<?php
 namespace App\Scopes;

use Illuminate\Database\Eloquent\{Scope, Model, Builder};

class AccountScope implements Scope
{
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function apply(Builder $builder, Model $model)
    {
      $account_id = (isset($this) && $this->user !== null) ? $this->user->account_id : 1;

      $builder->where('account_id', '=', $account_id);
    }
}