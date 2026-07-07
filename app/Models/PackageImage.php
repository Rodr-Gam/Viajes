<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PackageImage extends Model
{
    protected $fillable = ['package_id', 'user_id', 'image_name', 'url'];

    protected function url(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if (!$value) {
                    return null;
                }

                if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                    return $value;
                }

                return asset('storage/' . $value);
            },
            set: fn (?string $value) => $value,
        );
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deleteStoredFile(): void
    {
        $path = $this->getRawOriginal('url');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = ltrim(parse_url($path, PHP_URL_PATH) ?? '', '/');
            $path = preg_replace('#^storage/#', '', $path);
        }

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}