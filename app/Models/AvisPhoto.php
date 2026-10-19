<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvisPhoto extends Model
{
    protected $table = 'avis_photos';

    protected $fillable = ['avis_id', 'path', 'ordre'];

    public function avis(): BelongsTo
    {
        return $this->belongsTo(Avis::class);
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }
}
