<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invitation extends Model
{
    protected $fillable = ['campaign_id','brand_id','creator_id','status','turn','max_rounds','proposed_rate','currency','deliverables','deadline','message'];
    protected function casts(): array
    {
        return ['proposed_rate' => 'decimal:2', 'deliverables' => 'array', 'deadline' => 'date'];
    }
    public function campaign(): BelongsTo { return $this->belongsTo(Campaign::class); }
    public function brand(): BelongsTo { return $this->belongsTo(User::class, 'brand_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'creator_id'); }
    public function offers(): HasMany { return $this->hasMany(Offer::class)->orderBy('round'); }
    public function canBeSeenBy(User $user): bool
    {
        return $user->role === 'admin' || $user->id === $this->brand_id || $user->id === $this->creator_id;
    }
}
