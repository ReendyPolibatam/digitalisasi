<?php

use App\Models\Document;
use App\Models\User;
use App\Services\OcrService;
use App\Services\PdfPageService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->staff = User::factory()->create(['role' => 'staff']);

    // PdfPageService palsu: catat halaman yang diambil, tulis file PDF tiruan
    $this->extracted = [];
    $this->instance(PdfPageService::class, new class($this) extends PdfPageService
    {
        public function __construct(private $test) {}

        public function extract(string $source, array $pageNumbers, string $destination): void
        {
            $this->test->extracted[basename($destination)] = $pageNumbers;
            file_put_contents($destination, '%PDF-fake');
        }
    });
});

function fakeOcrPages(array $pages): void
{
    test()->mock(OcrService::class)->shouldReceive('pages')->once()->andReturn($pages);
}

function uploadedDocument(User $user, string $fileName): Document
{
    $path = 'documents/'.$fileName;
    Storage::disk('public')->put($path, 'original');

    return Document::create([
        'file_name' => $fileName,
        'file_path' => $path,
        'status' => 'draft',
        'user_id' => $user->id,
    ]);
}

test('a pdf with several categories is split into one document per category', function () {
    $document = uploadedDocument($this->staff, 'gabungan.pdf');

    fakeOcrPages([
        'CERTIFICATE OF QUANTITY LOADED  quantity loaded',
        'CERTIFICATE OF QUANTITY LOADED  quantity loaded',
        'BILL OF LADING  OB Putra Gandus',
        'MANIFEST',
        'SUMMARY DATA  Discharge Commenced',
    ]);

    $this->actingAs($this->staff)
        ->post(route('documents.ocr', $document->id))
        ->assertSessionHas('success');

    $documents = Document::orderBy('id')->get();

    expect($documents)->toHaveCount(3)
        ->and($documents->pluck('category')->all())->toBe(['loading', 'invoice', 'bongkar'])
        ->and($documents->pluck('file_name')->all())->toBe([
            'gabungan - Dokumen Loading.pdf',
            'gabungan - Invoice.pdf',
            'gabungan - Dokumen Bongkar.pdf',
        ])
        ->and(array_values($this->extracted))->toBe([[1, 2], [3, 4], [5]])
        ->and($documents->pluck('status')->unique()->all())->toBe(['draft'])
        ->and($documents->pluck('user_id')->unique()->all())->toBe([$this->staff->id]);

    // Dokumen asli dipakai untuk grup pertama, file aslinya diganti hasil pemecahan
    expect($documents->first()->id)->toBe($document->id);
    Storage::disk('public')->assertMissing('documents/gabungan.pdf');
    $documents->each(fn (Document $d) => Storage::disk('public')->assertExists($d->file_path));

    expect($documents[1]->ocr_text)->toContain('BILL OF LADING')->toContain('MANIFEST');
});

test('a pdf with a single category is not split', function () {
    $document = uploadedDocument($this->staff, 'invoice.pdf');

    fakeOcrPages(['BILL OF LADING', 'MANIFEST']);

    $this->actingAs($this->staff)
        ->post(route('documents.ocr', $document->id))
        ->assertSessionHas('success');

    expect(Document::count())->toBe(1)
        ->and($document->fresh()->category)->toBe('invoice')
        ->and($document->fresh()->file_path)->toBe('documents/invoice.pdf')
        ->and($this->extracted)->toBe([]);
});

test('submitting keeps the category decided per page', function () {
    $document = uploadedDocument($this->staff, 'invoice.pdf');
    $document->update(['ocr_text' => 'BILL OF LADING loading loading loading loading', 'category' => 'invoice']);

    $this->actingAs($this->staff)
        ->post(route('documents.submit', $document->id))
        ->assertRedirect(route('documents.index'));

    expect($document->fresh())
        ->status->toBe('pending')
        ->category->toBe('invoice');
});
