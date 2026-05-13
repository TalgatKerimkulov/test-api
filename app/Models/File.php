<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function url(): ?string
    {
        if (! Storage::disk($this->disk)->exists($this->path)) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->path);
    }
}
