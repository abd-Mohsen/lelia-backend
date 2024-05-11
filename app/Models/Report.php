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
        'type',
        'size',
        'neighborhood',
        'street',
        'landline_number',
        'mobile_number',
        'longitude',
        'latitude',
        'status',
        'date',
        'notes',
        'user_id'
    ];

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
        ];
    }


    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
