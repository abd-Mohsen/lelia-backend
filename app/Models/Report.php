<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'title',
        //
    ];

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
        ];
    }
}
