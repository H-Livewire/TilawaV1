<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    /** Daily-ayah reminder choices in the same shape the browser uses. */
    public function dailyAyahPreferences(): array
    {
        return [
            'enabled' => (bool) $this->daily_ayah_enabled,
            'time' => $this->daily_ayah_time ?: '07:00',
            // Notifications show the translation and reference; the Arabic is one tap away in the reader.
            'arabic' => false,
            'translation' => (bool) ($this->daily_ayah_translation ?? true),
            'reference' => (bool) ($this->daily_ayah_reference ?? true),
            'simple' => (bool) $this->daily_ayah_simple,
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
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
            'last_read_at' => 'datetime',
            'last_read_surah' => 'integer',
            'last_read_ayah' => 'integer',
            'tajweed_enabled' => 'boolean',
            'daily_ayah_enabled' => 'boolean',
            'daily_ayah_arabic' => 'boolean',
            'daily_ayah_translation' => 'boolean',
            'daily_ayah_reference' => 'boolean',
            'daily_ayah_simple' => 'boolean',
            'daily_ayah_last_sent_on' => 'date',
        ];
    }
}
