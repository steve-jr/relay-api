<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    protected $fillable = ['invitation_id','round','by','fee','currency','deliverables','deadline','note'];
    protected function casts(): array
    {
        return ['fee' => 'decimal:2', 'deliverables' => 'array', 'deadline' => 'date'];
    }
    public function invitation(): BelongsTo { return $this->belongsTo(Invitation::class); }
}
