<?php

namespace App;

use App\Exceptions\AccountAlreadyLinkedException;
use App\Exceptions\SourceProductClassDoesNotExistsException;
use App\Sourcing\AmazonAPI;
use App\Support\TemplateType;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    public function isDeveloper(): bool
    {
        $developers = app('developers') ?: [];

        return in_array($this['email'], $developers);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_user', 'user_id', 'product_id');
    }

    public function googleAccounts()
    {
        return $this->hasMany(GoogleAccount::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Account::class);
    }

    public function items()
    {
        return $this->hasManyThrough(Item::class, Account::class);
    }

    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    public function itemDescriptionTemplates()
    {
        return $this->templates()->where('type', TemplateType::ITEM_DESCRIPTION);
    }

    public function addAccount($username, $token): Account
    {
        if (Account::exists($username)) {
            throw new AccountAlreadyLinkedException;
        }

        return $this->accounts()->create(
            compact('username', 'token')
        );
    }

    public function addTemplate($name, $type, $content): Template
    {
        return $this->templates()->create(compact('name', 'type', 'content'));
    }

    public function updateOrCreateProduct(array $data): Product
    {
        return $this->products()->updateOrCreate(
            ['type' => $data['type'], 'source_id' => $data['id']],
            $data
        );
    }

    public function trackProduct($id, $type = AmazonCom::class): Product
    {
        if ( ! class_exists($type)) {
            throw new SourceProductClassDoesNotExistsException($type);
        }

        /** @var AmazonAPI $scraper */
        $scraper = new $type($id);

        $data = $scraper->scrape();

        return $this->updateOrCreateProduct($data);
    }
}
