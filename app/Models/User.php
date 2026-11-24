<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * PENTING: 'role' dan 'nip' wajib ada di sini
     * agar data tidak diblokir saat disimpan.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'role',
        'telegram_chat_id',
        'telegram_bind_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function adminlte_profile_url()
    {
        return 'profile';
    }
}