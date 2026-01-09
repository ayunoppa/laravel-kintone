<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class OAuthToken extends Model
{
    use HasUlids;

    protected $table = 'oauth_tokens';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'provider',
        'access_token',
        'refresh_token',
        'token_type',
        'scope',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 暗号化して保存（DBには平文で残さない）
     */
    public function setAccessTokenAttribute(string $value): void
    {
        $this->attributes['access_token'] = Crypt::encryptString($value);
    }

    public function getAccessTokenAttribute(): string
    {
        return Crypt::decryptString($this->attributes['access_token']);
    }

    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token'] = $value === null
            ? null
            : Crypt::encryptString($value);
    }

    public function getRefreshTokenAttribute(): ?string
    {
        $raw = $this->attributes['refresh_token'] ?? null;
        return $raw === null ? null : Crypt::decryptString($raw);
    }
}
