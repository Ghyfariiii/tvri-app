<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SPO;
use App\Models\MonitoringHarian;

class RevisiRequest extends Model
{
    use HasFactory;

    protected $table = 'revisi_requests';
    
    protected $fillable = [
        'diajukan_oleh',
        'disetujui_oleh', 
        'jenis_dokumen',
        'dokumen_id',
        'alasan_revisi',
        'status', 
        'tanggal_pengajuan',
        'tanggal_persetujuan'
    ];

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function penyetuju()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function spo()
    {
        return $this->belongsTo(SPO::class, 'dokumen_id');
    }

    public function monitoring()
    {
        return $this->belongsTo(MonitoringHarian::class, 'dokumen_id');
    }
}
