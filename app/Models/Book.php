<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'publisher_id',
        'publication_year',
        'page_count',
        'language',
        'isbn',
        'cover_image',
        'featured',
        'status',
    ];

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }
}
