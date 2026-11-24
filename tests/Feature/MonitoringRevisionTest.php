<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MonitoringHarian;
use App\Models\RevisiRequest;
use App\Models\SPO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class MonitoringRevisionTest extends TestCase
{
    use RefreshDatabase;

    private $pdUser;
    private $katimUser;
    private $adminUser;
    private $spo;
    private $monitoring;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->pdUser = User::factory()->create([
            'role' => 'pd',
            'name' => 'PD User',
            'email' => 'pd@test.com'
        ]);

        $this->katimUser = User::factory()->create([
            'role' => 'katim',
            'name' => 'Katim User',
            'email' => 'katim@test.com'
        ]);

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@test.com'
        ]);

        // Create SPO
        $this->spo = SPO::create([
            'no_surat' => 'SPO-001/2026',
            'tanggal_pelaksanaan' => now(),
            'hari' => now()->isoFormat('dddd'),
            'petugas_penyiaran' => json_encode([]),
            'petugas_penunjang' => json_encode([]),
            'created_by' => $this->adminUser->id,
            'status_persetujuan' => 'approved',
            'disetujui_oleh' => $this->adminUser->id,
            'catatan' => 'Test SPO'
        ]);

        // Create Monitoring
        $this->monitoring = MonitoringHarian::create([
            'tanggal' => now()->toDateString(),
            'spo_id' => $this->spo->id,
            'created_by' => $this->pdUser->id,
            'kru_bertugas' => json_encode([]),
            'kondisi_audio' => 'baik',
            'kondisi_video' => 'baik',
            'status_siaran' => 'lancar'
        ]);
    }

    /**
     * TEST: requestRevision - Create new revision request
     */
    public function test_request_revision_creates_revision_request()
    {
        $this->actingAs($this->pdUser);

        $response = $this->post(route('monitoring.request-revision', $this->monitoring->id), [
            'alasan_revisi' => 'Data kru bertugas kurang lengkap'
        ]);

        $response->assertSessionHas('success', 'Permintaan revisi monitoring berhasil dikirim.');

        $this->assertDatabaseHas('revisi_requests', [
            'monitoring_id' => $this->monitoring->id,
            'diajukan_oleh' => $this->pdUser->id,
            'alasan_revisi' => 'Data kru bertugas kurang lengkap',
            'status' => 'pending'
        ]);
    }

    /**
     * TEST: requestRevision - Validation fails without alasan_revisi
     */
    public function test_request_revision_requires_alasan_revisi()
    {
        $this->actingAs($this->pdUser);

        $response = $this->post(route('monitoring.request-revision', $this->monitoring->id), [
            'alasan_revisi' => ''
        ]);

        $response->assertSessionHasErrors('alasan_revisi');
    }

    /**
     * TEST: requestRevision - Max length validation
     */
    public function test_request_revision_max_length_validation()
    {
        $this->actingAs($this->pdUser);

        $longText = str_repeat('a', 501);

        $response = $this->post(route('monitoring.request-revision', $this->monitoring->id), [
            'alasan_revisi' => $longText
        ]);

        $response->assertSessionHasErrors('alasan_revisi');
    }

    /**
     * TEST: requestRevision - Non-owner cannot request revision
     */
    public function test_request_revision_non_owner_denied()
    {
        $otherPdUser = User::factory()->create(['role' => 'pd']);
        $this->actingAs($otherPdUser);

        $response = $this->post(route('monitoring.request-revision', $this->monitoring->id), [
            'alasan_revisi' => 'Test'
        ]);

        $response->assertSessionHas('error');
    }

    /**
     * TEST: requestRevision - Admin can request revision (any monitoring)
     */
    public function test_admin_can_request_revision_any_monitoring()
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('monitoring.request-revision', $this->monitoring->id), [
            'alasan_revisi' => 'Admin requesting revision'
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('revisi_requests', [
            'monitoring_id' => $this->monitoring->id,
            'diajukan_oleh' => $this->adminUser->id,
            'status' => 'pending'
        ]);
    }

    /**
     * TEST: requestRevision - 404 for non-existent monitoring
     */
    public function test_request_revision_monitoring_not_found()
    {
        $this->actingAs($this->pdUser);

        $response = $this->post(route('monitoring.request-revision', 9999), [
            'alasan_revisi' => 'Test'
        ]);

        $response->assertNotFound();
    }

    /**
     * TEST: approveRevision - Katim can approve
     */
    public function test_approve_revision_by_katim()
    {
        // Create revision request
        $revisi = RevisiRequest::create([
            'monitoring_id' => $this->monitoring->id,
            'diajukan_oleh' => $this->pdUser->id,
            'alasan_revisi' => 'Test revision',
            'status' => 'pending'
        ]);

        $this->actingAs($this->katimUser);

        $response = $this->patch(route('monitoring.approve-revision', $revisi->id));

        $response->assertSessionHas('success', 'Permintaan revisi disetujui.');

        $this->assertDatabaseHas('revisi_requests', [
            'id' => $revisi->id,
            'status' => 'disetujui',
            'disetujui_oleh' => $this->katimUser->id
        ]);
    }

    /**
     * TEST: approveRevision - Admin can approve
     */
    public function test_approve_revision_by_admin()
    {
        $revisi = RevisiRequest::create([
            'monitoring_id' => $this->monitoring->id,
            'diajukan_oleh' => $this->pdUser->id,
            'alasan_revisi' => 'Test revision',
            'status' => 'pending'
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->patch(route('monitoring.approve-revision', $revisi->id));

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('revisi_requests', [
            'id' => $revisi->id,
            'status' => 'disetujui'
        ]);
    }

    /**
     * TEST: approveRevision - Non-katim/admin denied
     */
    public function test_approve_revision_non_katim_denied()
    {
        $revisi = RevisiRequest::create([
            'monitoring_id' => $this->monitoring->id,
            'diajukan_oleh' => $this->pdUser->id,
            'alasan_revisi' => 'Test revision',
            'status' => 'pending'
        ]);

        $this->actingAs($this->pdUser);

        $response = $this->patch(route('monitoring.approve-revision', $revisi->id));

        $response->assertForbidden();
    }

    /**
     * TEST: approveRevision - 404 for non-existent revision
     */
    public function test_approve_revision_not_found()
    {
        $this->actingAs($this->katimUser);

        $response = $this->patch(route('monitoring.approve-revision', 9999));

        $response->assertNotFound();
    }

    /**
     * TEST: rejectRevision - Katim can reject
     */
    public function test_reject_revision_by_katim()
    {
        $revisi = RevisiRequest::create([
            'monitoring_id' => $this->monitoring->id,
            'diajukan_oleh' => $this->pdUser->id,
            'alasan_revisi' => 'Test revision',
            'status' => 'pending'
        ]);

        $this->actingAs($this->katimUser);

        $response = $this->patch(route('monitoring.reject-revision', $revisi->id));

        $response->assertSessionHas('error', 'Permintaan revisi berhasil ditolak.');

        $this->assertDatabaseHas('revisi_requests', [
            'id' => $revisi->id,
            'status' => 'ditolak',
            'disetujui_oleh' => $this->katimUser->id
        ]);
    }

    /**
     * TEST: rejectRevision - Admin can reject
     */
    public function test_reject_revision_by_admin()
    {
        $revisi = RevisiRequest::create([
            'monitoring_id' => $this->monitoring->id,
            'diajukan_oleh' => $this->pdUser->id,
            'alasan_revisi' => 'Test revision',
            'status' => 'pending'
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->patch(route('monitoring.reject-revision', $revisi->id));

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('revisi_requests', [
            'id' => $revisi->id,
            'status' => 'ditolak'
        ]);
    }

    /**
     * TEST: rejectRevision - Non-katim/admin denied
     */
    public function test_reject_revision_non_katim_denied()
    {
        $revisi = RevisiRequest::create([
            'monitoring_id' => $this->monitoring->id,
            'diajukan_oleh' => $this->pdUser->id,
            'alasan_revisi' => 'Test revision',
            'status' => 'pending'
        ]);

        $this->actingAs($this->pdUser);

        $response = $this->patch(route('monitoring.reject-revision', $revisi->id));

        $response->assertForbidden();
    }

    /**
     * TEST: rejectRevision - 404 for non-existent revision
     */
    public function test_reject_revision_not_found()
    {
        $this->actingAs($this->katimUser);

        $response = $this->patch(route('monitoring.reject-revision', 9999));

        $response->assertNotFound();
    }

    /**
     * TEST: Revisi hanya bisa dilakukan saat status pending
     */
    public function test_approve_non_pending_revision_fails()
    {
        $revisi = RevisiRequest::create([
            'monitoring_id' => $this->monitoring->id,
            'diajukan_oleh' => $this->pdUser->id,
            'alasan_revisi' => 'Test revision',
            'status' => 'disetujui'
        ]);

        $this->actingAs($this->katimUser);

        $response = $this->patch(route('monitoring.approve-revision', $revisi->id));

        $response->assertSessionHas('error', 'Tidak ada permintaan revisi pending.');
    }

    /**
     * TEST: timestamp fields are set correctly
     */
    public function test_revision_timestamps_are_set()
    {
        $revisi = RevisiRequest::create([
            'monitoring_id' => $this->monitoring->id,
            'diajukan_oleh' => $this->pdUser->id,
            'alasan_revisi' => 'Test revision',
            'status' => 'pending',
            'tanggal_pengajuan' => now()
        ]);

        $this->actingAs($this->katimUser);
        $this->patch(route('monitoring.approve-revision', $revisi->id));

        $updated = RevisiRequest::find($revisi->id);
        $this->assertNotNull($updated->tanggal_persetujuan);
    }
}
