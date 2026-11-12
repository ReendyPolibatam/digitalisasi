<?php

use App\Models\Document;
use App\Models\User;
use App\Services\DocumentFieldExtractorService;

beforeEach(function () {
    $this->staff = User::factory()->create(['role' => 'staff']);
    $this->admin = User::factory()->create(['role' => 'admin']);
});

/**
 * @param  array<string, mixed>  $attributes
 */
function voyageDoc(User $owner, array $attributes): Document
{
    return Document::create(array_merge([
        'file_name' => 'dok.pdf',
        'file_path' => 'documents/dok.pdf',
        'status' => 'approved',
        'user_id' => $owner->id,
        'vessel_name' => 'OB PUTRA GANDUS',
    ], $attributes));
}

test('tanggal dalam berbagai format terbaca dari teks', function () {
    $dates = (new DocumentFieldExtractorService)->documentDates("Pakning, May 31, 2026\nDATE : 30-May-26\n29/05/2026\n28 May 2026");

    expect($dates)->toEqualCanonicalizing(['2026-05-31', '2026-05-30', '2026-05-29', '2026-05-28']);
});

test('invoice tanpa voyage masuk ke voyage kapal yang sama dengan tanggal berdekatan', function () {
    voyageDoc($this->staff, ['file_name' => 'Loading.pdf', 'category' => 'loading', 'voyage' => '022/L/P.G/PPN/ADO/V/2026', 'loading_date' => '2026-05-31']);
    voyageDoc($this->staff, ['file_name' => 'Invoice.pdf', 'category' => 'invoice', 'ocr_text' => 'MANIFEST  Pakning, May 31, 2026']);

    $this->actingAs($this->admin)->get('/admin/library/OBPUTRAGANDUS/022PGPPNADOV2026')
        ->assertOk()
        ->assertSee('Invoice.pdf')
        ->assertSee('voyage ditentukan dari tanggal dokumen');
});

test('invoice dengan tanggal yang jauh tidak dimasukkan ke voyage tersebut', function () {
    voyageDoc($this->staff, ['file_name' => 'Loading.pdf', 'category' => 'loading', 'voyage' => '022/L/P.G/PPN/ADO/V/2026', 'loading_date' => '2026-05-31']);
    voyageDoc($this->staff, ['file_name' => 'Invoice.pdf', 'category' => 'invoice', 'ocr_text' => 'MANIFEST  Pakning, August 20, 2026']);

    $this->actingAs($this->admin)->get('/admin/library/OBPUTRAGANDUS/022PGPPNADOV2026')
        ->assertOk()
        ->assertDontSee('Invoice.pdf');
});

test('invoice kapal lain tidak masuk ke voyage tersebut', function () {
    voyageDoc($this->staff, ['file_name' => 'Loading.pdf', 'category' => 'loading', 'voyage' => '022/L/P.G/PPN/ADO/V/2026', 'loading_date' => '2026-05-31']);
    voyageDoc($this->staff, ['file_name' => 'Invoice.pdf', 'category' => 'invoice', 'vessel_name' => 'TKG CAHAYA ALAM', 'ocr_text' => 'May 31, 2026']);

    $this->actingAs($this->admin)->get('/admin/library/OBPUTRAGANDUS/022PGPPNADOV2026')
        ->assertOk()
        ->assertDontSee('Invoice.pdf');
});

test('voyage terdekat yang dipilih jika kapal punya beberapa voyage', function () {
    voyageDoc($this->staff, ['file_name' => 'Voyage22.pdf', 'category' => 'loading', 'voyage' => '022/L/P.G/V/2026', 'loading_date' => '2026-05-31']);
    voyageDoc($this->staff, ['file_name' => 'Voyage23.pdf', 'category' => 'loading', 'voyage' => '023/L/P.G/V/2026', 'loading_date' => '2026-06-08']);
    voyageDoc($this->staff, ['file_name' => 'Invoice.pdf', 'category' => 'invoice', 'ocr_text' => 'June 7, 2026']);

    $this->actingAs($this->admin)->get('/admin/library/OBPUTRAGANDUS/023PGV2026')->assertSee('Invoice.pdf');
    $this->actingAs($this->admin)->get('/admin/library/OBPUTRAGANDUS/022PGV2026')->assertDontSee('Invoice.pdf');
});

test('nama kapal tidak ikut membawa kata Order dari kolom sebelahnya', function () {
    $vessel = (new DocumentFieldExtractorService)->extract('Vessel : OB. PUTRA GANDUS Order No : 0054/KP145120')['vessel_name'];

    expect($vessel)->toBe('OB. PUTRA GANDUS');
});
