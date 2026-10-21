<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Plan;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// plan and signed_in are server-controlled, so they are deliberately not mass assignable.
#[Fillable(['name', 'email', 'password', 'backup_on', 'last_backup_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Mirror the schema defaults so a new model reads correctly before it is saved.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'plan' => 'free',
        'signed_in' => false,
        'backup_on' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'plan' => Plan::class,
            'signed_in' => 'boolean',
            'backup_on' => 'boolean',
            'last_backup_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Calendar, $this>
     */
    public function calendars(): HasMany
    {
        return $this->hasMany(Calendar::class, 'owner_id');
    }

    /**
     * @return HasOne<Setting, $this>
     */
    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    public function isPro(): bool
    {
        return $this->plan === Plan::Pro;
    }
}
