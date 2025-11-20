<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Blog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = ['meta_keywords' => 'array'];

    public function details()
    {
        return $this->hasOne(BlogDetails::class, 'blog_id');
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class);
    }

    public function getLanguageEditClass($id, $languageId)
    {
        return DB::table('blog_details')->where(['blog_id' => $id, 'language_id' => $languageId])->exists() ? 'bi-check2' : 'bi-pencil';
    }


    public function getStatusMessageAttribute()
    {
        if ($this->status == 0) {
            return '<span class="badge bg-soft-warning text-warning"> <span class="legend-indicator bg-warning"></span>' . trans('Inactive') . '</span>';
        }
        return '<span class="badge bg-soft-success text-success"> <span class="legend-indicator bg-success"></span>' . trans('Active') . '</span>';
    }

}
