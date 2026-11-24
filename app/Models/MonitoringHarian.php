<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoringHarian extends Model
{
    use HasFactory;

    protected $table = 'monitoring_harians';

    protected $fillable = [
        'tanggal',
        'spo_id',
        'created_by',
        'kru_bertugas',
        'kondisi_audio',
        'kondisi_video',
        'catatan_khusus',
        'status_siaran',
        'status_revisi',
    ];

    protected $casts = [
        'kru_bertugas' => 'array',
        'tanggal' => 'date',
    ];

    public function spo()
    {
        return $this->belongsTo(SPO::class, 'spo_id');
    }

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
