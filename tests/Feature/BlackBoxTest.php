<?php

/*
|--------------------------------------------------------------------------
| Pengujian Black Box Testing
|--------------------------------------------------------------------------
|
| Skenario mengikuti Tabel 4.1 (Pengujian Fungsional Staff) dan Tabel 4.2
| (Pengujian Fungsional Admin) pada laporan. Setiap pengujian hanya melihat
| input dan output aplikasi (request HTTP -> respons/data), tanpa melihat
| kode di dalamnya. OCR diganti tiruan agar pengujian tidak bergantung pada
| Tesseract dan kualitas gambar.
|
| Jalankan: php artisan test --compact tests/Feature/BlackBoxTest.php
|
*/

use App\Models\Document;
use App\Models\User;
use App\Services\OcrService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->staff = User::factory()->create(['role' => 'staff', 'email' => 'staff@contoh.com']);
    $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@contoh.com']);
});

/**
 * Dokumen milik Staff dengan berkas tiruan di storage.
 *
 * @param  array<string, mixed>  $attributes
 */
function bbDocument(User $owner, array $attributes = []): Document
{
    Storage::disk('public')->put('documents/uji.pdf', '%PDF-uji');

    return Document::create(array_merge([
        'file_name' => 'uji.pdf',
        'file_path' => 'documents/uji.pdf',
        'status' => 'draft',
        'user_id' => $owner->id,
    ], $attributes));
}

/**
 * OCR tiruan yang mengembalikan teks per halaman.
 *
 * @param  list<string>  $pages
 */
function bbOcrReturns(array $pages): void
{
    test()->mock(OcrService::class)->shouldReceive('pages')->andReturn($pages);
}

/* ======================= Tabel 4.1 Pengujian Fungsional Staff ======================= */

describe('Staff: Login', function () {
    test('login dengan email dan password valid diarahkan ke dashboard Staff', function () {
        $this->post('/login', ['email' => 'staff@contoh.com', 'password' => 'password'])
            ->assertRedirect('/staff');

        $this->assertAuthenticatedAs($this->staff);
    });

    test('email atau password salah ditolak dengan pesan kesalahan', function () {
        $this->from('/login')
            ->post('/login', ['email' => 'staff@contoh.com', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    });

    test('kolom login kosong menampilkan peringatan data belum lengkap', function () {
        $this->from('/login')
            ->post('/login', ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    });
});

describe('Staff: Logout', function () {
    test('logout mengakhiri sesi', function () {
        $this->actingAs($this->staff)->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    });

    test('mengakses sistem tanpa sesi diarahkan ke halaman login', function () {
        $this->get('/staff')->assertRedirect('/login');
    });
});

describe('Staff: Upload dokumen', function () {
    test('dokumen PDF atau gambar berhasil diunggah dan tersimpan', function () {
        $this->actingAs($this->staff)
            ->post('/documents', ['file' => UploadedFile::fake()->create('bl.pdf', 200, 'application/pdf')])
            ->assertSessionHas('success');

        $document = Document::first();
        expect($document->file_name)->toBe('bl.pdf')->and($document->status)->toBe('draft');
        Storage::disk('public')->assertExists($document->file_path);
    });

    test('file dengan format tidak didukung ditolak', function () {
        $this->actingAs($this->staff)
            ->post('/documents', ['file' => UploadedFile::fake()->create('data.docx', 100)])
            ->assertSessionHasErrors('file');

        expect(Document::count())->toBe(0);
    });

    test('file melebihi 10 MB ditolak dan tidak tersimpan', function () {
        $this->actingAs($this->staff)
            ->post('/documents', ['file' => UploadedFile::fake()->create('besar.pdf', 11000, 'application/pdf')])
            ->assertSessionHasErrors('file');

        expect(Document::count())->toBe(0);
    });
});

describe('Staff: Ekstraksi teks (OCR) dan klasifikasi', function () {
    test('dokumen yang jelas diekstraksi dan teksnya disimpan', function () {
        $document = bbDocument($this->staff);
        bbOcrReturns(['BILL OF LADING  Name of Vessel : OB CONTOH KAPAL']);

        $this->actingAs($this->staff)->post("/documents/{$document->id}/ocr")->assertSessionHas('success');

        expect($document->fresh()->ocr_text)->toContain('BILL OF LADING');
    });

    test('dokumen tidak terbaca memberi notifikasi OCR gagal', function () {
        $document = bbDocument($this->staff);
        bbOcrReturns(['']);

        $this->actingAs($this->staff)->post("/documents/{$document->id}/ocr")->assertSessionHas('error');

        expect($document->fresh()->ocr_text)->toBeNull();
    });

    test('jenis dokumen yang dikenali disimpan otomatis', function (string $text, string $category) {
        $document = bbDocument($this->staff);
        bbOcrReturns([$text]);

        $this->actingAs($this->staff)->post("/documents/{$document->id}/ocr");

        expect($document->fresh()->category)->toBe($category);
    })->with([
        'Invoice' => ['MANIFEST  Port of Loading : Sungai Pakning', 'invoice'],
        'Bongkar' => ['SUMMARY DATA  Discharge Commenced  Discharging', 'bongkar'],
        'Loading' => ['CERTIFICATE OF QUANTITY LOADED  quantity loaded', 'loading'],
    ]);

    test('jenis dokumen tidak dikenali masuk kategori tidak terklasifikasi', function () {
        $document = bbDocument($this->staff);
        bbOcrReturns(['Surat undangan rapat bulanan']);

        $this->actingAs($this->staff)->post("/documents/{$document->id}/ocr")
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'Tidak Terklasifikasi'));

        expect($document->fresh()->category)->toBeNull();
    });
});

describe('Staff: Review dan edit hasil OCR', function () {
    test('perubahan hasil OCR tersimpan', function () {
        $document = bbDocument($this->staff, ['ocr_text' => 'teks asli']);

        $this->actingAs($this->staff)
            ->post("/documents/{$document->id}/ocr-text", ['ocr_text' => 'teks perbaikan'])
            ->assertSessionHas('success');

        expect($document->fresh()->ocr_text_edited)->toBe('teks perbaikan');
    });

    test('hasil OCR yang sudah sesuai dapat langsung diajukan tanpa diedit', function () {
        $document = bbDocument($this->staff, ['ocr_text' => 'MANIFEST', 'category' => 'invoice']);

        $this->actingAs($this->staff)->post("/documents/{$document->id}/submit")->assertSessionHas('success');

        expect($document->fresh())->status->toBe('pending')->ocr_text_edited->toBeNull();
    });

    test('menyimpan hasil OCR kosong ditolak', function () {
        $document = bbDocument($this->staff, ['ocr_text' => 'teks asli']);

        $this->actingAs($this->staff)
            ->post("/documents/{$document->id}/ocr-text", ['ocr_text' => ''])
            ->assertSessionHasErrors('ocr_text');

        expect($document->fresh()->ocr_text_edited)->toBeNull();
    });
});

describe('Staff: Mengajukan verifikasi', function () {
    test('dokumen lengkap diajukan dan muncul di daftar verifikasi Admin', function () {
        $document = bbDocument($this->staff, ['ocr_text' => 'MANIFEST', 'category' => 'invoice']);

        $this->actingAs($this->staff)->post("/documents/{$document->id}/submit")
            ->assertRedirect(route('documents.index'))
            ->assertSessionHas('success');

        expect($document->fresh()->status)->toBe('pending');
        $this->actingAs($this->admin)->get('/admin/documents')->assertOk()->assertSee('uji.pdf');
    });

    test('dokumen yang belum di-OCR ditolak saat diajukan', function () {
        $document = bbDocument($this->staff);

        $this->actingAs($this->staff)->post("/documents/{$document->id}/submit")->assertSessionHas('error');

        expect($document->fresh()->status)->toBe('draft');
    });
});

describe('Staff: Dokumen ditolak', function () {
    test('dokumen ditolak menampilkan catatan admin dan statusnya tetap rejected', function () {
        bbDocument($this->staff, ['ocr_text' => 'MANIFEST', 'status' => 'rejected', 'admin_note' => 'tanggal tidak terbaca']);

        $this->actingAs($this->staff)->get('/documents')->assertOk()->assertSee('tanggal tidak terbaca');

        expect(Document::first()->status)->toBe('rejected');
    });

    test('dokumen yang sudah diajukan tidak dapat diedit lagi', function () {
        $document = bbDocument($this->staff, ['ocr_text' => 'MANIFEST', 'status' => 'pending']);

        $this->actingAs($this->staff)
            ->post("/documents/{$document->id}/ocr-text", ['ocr_text' => 'ubah'])
            ->assertForbidden();
    });
});

describe('Staff: Daftar dan detail dokumen', function () {
    test('daftar dokumen menampilkan dokumen milik Staff', function () {
        bbDocument($this->staff, ['ocr_text' => 'MANIFEST', 'vessel_name' => 'OB CONTOH KAPAL']);

        $this->actingAs($this->staff)->get('/documents')->assertOk()->assertSee('OB CONTOH KAPAL');
    });

    test('detail dokumen menampilkan hasil OCR dan klasifikasi', function () {
        $document = bbDocument($this->staff, ['ocr_text' => 'teks hasil ocr', 'category' => 'invoice']);

        $this->actingAs($this->staff)->get("/documents/{$document->id}")
            ->assertOk()->assertSee('teks hasil ocr')->assertSee('Invoice');
    });

    test('Staff tidak dapat membuka dokumen milik Staff lain', function () {
        $other = User::factory()->create(['role' => 'staff']);
        $document = bbDocument($other);

        $this->actingAs($this->staff)->get("/documents/{$document->id}")->assertForbidden();
    });
});

/* ======================= Tabel 4.2 Pengujian Fungsional Admin ======================= */

describe('Admin: Login dan logout', function () {
    test('login Admin valid diarahkan ke dashboard Admin', function () {
        $this->post('/login', ['email' => 'admin@contoh.com', 'password' => 'password'])
            ->assertRedirect('/admin');
    });

    test('password Admin salah ditolak', function () {
        $this->post('/login', ['email' => 'admin@contoh.com', 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    });

    test('logout Admin mengakhiri sesi', function () {
        $this->actingAs($this->admin)->post('/logout');

        $this->assertGuest();
    });

    test('Staff tidak dapat membuka halaman Admin', function () {
        $this->actingAs($this->staff)->get('/admin')->assertForbidden();
    });
});

describe('Admin: Mengelola data pengguna', function () {
    test('menambahkan pengguna baru dengan data lengkap', function () {
        $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Staff Baru', 'email' => 'baru@contoh.com', 'role' => 'staff',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.users.index'));

        $this->actingAs($this->admin)->get(route('admin.users.index'))->assertSee('baru@contoh.com');
    });

    test('mengubah data pengguna', function () {
        $this->actingAs($this->admin)->put(route('admin.users.update', $this->staff), [
            'name' => 'Nama Diubah', 'email' => $this->staff->email, 'role' => 'staff',
        ])->assertRedirect(route('admin.users.index'));

        expect($this->staff->fresh()->name)->toBe('Nama Diubah');
    });

    test('menghapus data pengguna', function () {
        $user = User::factory()->create(['role' => 'staff']);

        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));

        $this->assertModelMissing($user);
    });

    test('data tidak lengkap atau email sudah terdaftar ditolak', function () {
        $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => '', 'email' => 'staff@contoh.com', 'role' => 'staff',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasErrors(['name', 'email']);

        expect(User::count())->toBe(2);
    });

    test('membatalkan aksi tidak mengubah data', function () {
        $this->actingAs($this->admin)->get(route('admin.users.edit', $this->staff))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.users.index'))->assertOk();

        expect($this->staff->fresh()->name)->toBe($this->staff->name);
    });
});

describe('Admin: Pencarian dan detail dokumen', function () {
    test('dokumen yang sudah diajukan dapat ditemukan di Library per kapal', function () {
        bbDocument($this->staff, ['ocr_text' => 'MANIFEST', 'status' => 'pending', 'vessel_name' => 'OB CONTOH KAPAL']);

        $this->actingAs($this->admin)->get('/admin/library')->assertOk()->assertSee('OB CONTOH KAPAL');
    });

    test('Library kapal yang tidak ada mengembalikan halaman tidak ditemukan', function () {
        $this->actingAs($this->admin)->get('/admin/library/TIDAKADA')->assertNotFound();
    });
});

describe('Admin: Verifikasi dan penetapan status dokumen', function () {
    test('daftar verifikasi dan detail dokumen dapat dibuka', function () {
        $document = bbDocument($this->staff, ['ocr_text' => 'teks diajukan', 'status' => 'pending']);

        $this->actingAs($this->admin)->get('/admin/documents')->assertOk()->assertSee('uji.pdf');
        $this->actingAs($this->admin)->get("/admin/documents/{$document->id}")->assertOk()->assertSee('teks diajukan');
    });

    test('approve menyimpan status, waktu, dan Admin yang memutuskan', function () {
        $document = bbDocument($this->staff, ['ocr_text' => 'MANIFEST', 'status' => 'pending']);

        $this->actingAs($this->admin)->post("/admin/documents/{$document->id}/approve")->assertSessionHas('success');

        expect($document->fresh())
            ->status->toBe('approved')
            ->approved_by->toBe($this->admin->id)
            ->approved_at->not->toBeNull();
    });

    test('reject menyimpan catatan penolakan untuk Staff', function () {
        $document = bbDocument($this->staff, ['ocr_text' => 'MANIFEST', 'status' => 'pending']);

        $this->actingAs($this->admin)
            ->post("/admin/documents/{$document->id}/reject", ['admin_note' => 'nomor voyage salah'])
            ->assertSessionHas('success');

        expect($document->fresh())->status->toBe('rejected')->admin_note->toBe('nomor voyage salah');
    });

    test('dokumen yang sudah diputuskan tidak dapat diputuskan ulang', function () {
        $document = bbDocument($this->staff, ['ocr_text' => 'MANIFEST', 'status' => 'approved']);

        $this->actingAs($this->admin)->post("/admin/documents/{$document->id}/reject")->assertSessionHas('error');

        expect($document->fresh()->status)->toBe('approved');
    });
});
