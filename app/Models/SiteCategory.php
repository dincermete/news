<?php

namespace App\Models;

use Database\Factories\SiteCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description'])]
class SiteCategory extends Model
{
    /** @use HasFactory<SiteCategoryFactory> */
    use HasFactory;

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
