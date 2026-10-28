<?php

use App\Models\Document;
use App\Models\User;

beforeEach(function () {
    $this->staff = User::factory()->create(['role' => 'staff']);
});

function staffDocument(User $user, array $attributes = []): Document
{
    return Document::create(array_merge([
        'file_name' => 'dokumen.pdf',
        'file_path' => 'documents/dokumen.pdf',
        'status' => 'draft',
        'user_id' => $user->id,
    ], $attributes));
}

test('the detail page shows an empty state before ocr', function () {
    $document = staffDocument($this->staff);

    $this->actingAs($this->staff)
        ->get(route('documents.show', $document->id))
        ->assertOk()
        ->assertSee('OCR belum dijalankan')
        ->assertDontSee('Edit Hasil OCR');
});

test('a draft with ocr text can be edited from the detail page', function () {
    $document = staffDocument($this->staff, ['ocr_text' => 'teks asli', 'category' => 'bongkar']);

    $this->actingAs($this->staff)
        ->get(route('documents.show', $document->id))
        ->assertOk()
        ->assertSee('Edit Hasil OCR')
        ->assertSee(route('documents.ocr.update', $document->id))
        ->assertSee('teks asli')
        ->assertSee('Dokumen Bongkar');
});

test('saved edits are shown instead of the original ocr text', function () {
    $document = staffDocument($this->staff, ['ocr_text' => 'teks asli']);

    $this->actingAs($this->staff)
        ->post(route('documents.ocr.update', $document->id), ['ocr_text' => "teks\r\nperbaikan"])
        ->assertSessionHas('success');

    expect($document->fresh()->ocr_text_edited)->toBe("teks\nperbaikan");

    $this->actingAs($this->staff)
        ->get(route('documents.show', $document->id))
        ->assertSee('Sudah diedit')
        ->assertSee("teks\nperbaikan", false);
});

test('submitted documents cannot be edited', function () {
    $document = staffDocument($this->staff, ['ocr_text' => 'teks asli', 'status' => 'pending']);

    $this->actingAs($this->staff)
        ->get(route('documents.show', $document->id))
        ->assertDontSee('Edit Hasil OCR');

    $this->actingAs($this->staff)
        ->post(route('documents.ocr.update', $document->id), ['ocr_text' => 'ubah'])
        ->assertForbidden();
});
