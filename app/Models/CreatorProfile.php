<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreatorProfile extends Model
{
    protected $fillable = [
        'user_id','slug','display_name','bio','city','country','currency','primary_niche',
        'secondary_niches','industries','socials','average_engagement_rate','starting_rate','profile_image_url','is_published'
    ];
    protected function casts(): array
    {
        return ['secondary_niches' => 'array', 'industries' => 'array', 'socials' => 'array',
            'average_engagement_rate' => 'decimal:2', 'starting_rate' => 'decimal:2', 'is_published' => 'boolean'];
    }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function invitations(): HasMany { return $this->hasMany(Invitation::class, 'creator_id', 'user_id'); }

    public function publicPayload(): array
    {
        return [
            'id' => $this->id, 'slug' => $this->slug, 'display_name' => $this->display_name,
            'bio' => $this->bio, 'city' => $this->city, 'country' => $this->country,
            'currency' => $this->currency, 'primary_niche' => $this->primary_niche,
            'secondary_niches' => $this->secondary_niches ?? [], 'industries' => $this->industries ?? [],
            'socials' => $this->socials ?? [], 'average_engagement_rate' => $this->average_engagement_rate,
            'starting_rate' => $this->starting_rate, 'profile_image_url' => $this->profile_image_url,
        ];
    }
}
