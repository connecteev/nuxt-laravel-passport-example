<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use SoftDeletes;

    public $table = 'tags';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        // 'tag_logo_image',
        // 'tag_background_image',
    ];

    protected $fillable = [
        'name',
        'slug',
        'about',
        'intro',
        'active',
        'cta_title',
        'updated_at',
        'created_at',
        'restricted',
        'deleted_at',
        'is_popular',
        'is_featured',
        'tag_fg_color',
        'tag_bg_color',
        'cta_subtitle',
        'popular_order',
        'featured_order',
        'submission_guidelines',
    ];


    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
