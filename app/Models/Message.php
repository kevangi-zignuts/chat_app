<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['message', 'sender_id', 'receiver_id'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($module) {
            $user = auth()->user();
            if ($user) {
                $module->created_by = $user->id;
            }
        });

        static::updating(function ($module) {
            $user = auth()->user();
            if ($user) {
                $module->updated_by = $user->id;
            }
        });
    }
}
