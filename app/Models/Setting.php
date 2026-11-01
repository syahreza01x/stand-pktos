<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::remember("setting_{$key}", 60, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting ? (string) $setting->value : $default;
        });
    }

    public static function set(string $key, ?string $value, ?string $description = null): static
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description ?? $key,
            ]
        );

        Cache::forget("setting_{$key}");

        return $setting;
    }
}
