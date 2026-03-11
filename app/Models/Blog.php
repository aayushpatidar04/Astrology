<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use SoftDeletes;

    protected $table = "blogs";

    protected $fillable = [
        'blog_category_id',
        'title',
        'slug',
        'meta_description',
        'short_description',
        'description1',
        'description2',
        'images',
        'tags',
    ];

    protected $casts = [
        'images' => 'array',
        'tags' => 'array'
    ];

    public function category(){
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }
}
