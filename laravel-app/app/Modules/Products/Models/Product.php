<?php

namespace App\Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'image_path',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected $appends = ['image_url', 'is_available',];

    // public function getImageUrlAttribute(): ?string
    // {
    //     return $this->image_path
    //         ? asset('storage/' . $this->image_path)
    //         : null;
    // }

    public function getIsAvailableAttribute(): ?bool
    {
        if ($this->stockQuantity === null) {
            return null;
        }

        return $this->stockQuantity > 0;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $this->image_path));

        if (preg_match('/^(https?:)?\/\//i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        if (str_starts_with($path, 'assets/') || str_starts_with($path, 'storage/')) {
            return '/' . $path;
        }

        return '/storage/' . $path;
    }}