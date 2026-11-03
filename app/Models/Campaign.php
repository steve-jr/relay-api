<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = ['brand_id','name','description','objective','budget','currency','country','status','start_date','end_date','deliverables'];
    protected function casts(): array
    {
        return ['budget' => 'decimal:2', 'start_date' => 'date', 'end_date' => 'date', 'deliverables' => 'array'];
    }
    public function brand(): BelongsTo { return $this->belongsTo(User::class, 'brand_id'); }
    public function invitations(): HasMany { return $this->hasMany(Invitation::class); }
}
