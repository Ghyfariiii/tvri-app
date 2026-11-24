<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPO extends Model
{
    use HasFactory;

    protected $table = 'spos';

    protected $fillable = [
        'parent_spo_id',
        'versi',
        'katim_id',
        'no_surat',
        'tanggal_pelaksanaan',
        'hari',
        'petugas_penyiaran',
        'petugas_penunjang',
        'created_by',
        'status_persetujuan',
        'disetujui_oleh_nip',
        'tanggal_disetujui',
        'alasan_penolakan',
    ];

    public function katim()
    {
        return $this->belongsTo(User::class, 'katim_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function monitoring()
    {
        return $this->hasOne(MonitoringHarian::class, 'spo_id');
    }
}
