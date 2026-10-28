@extends('layouts.staff')

@section('content')

<div class="max-w-3xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            Upload Dokumen
        </h1>
        <p class="text-gray-500 mt-2">
            Upload dokumen shipping untuk diproses OCR dan diverifikasi admin.
        </p>
    </div>

    <!-- Alert Sukses -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-xl mb-6">
            {{ session('success') }}
        </div>
    @endif

    <!-- Alert Error (dari server) -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-xl mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Upload Card -->
    <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">

        <form id="uploadForm"
              action="{{ route('documents.store') }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            <!-- Upload Area -->
            <div id="dropzone"
                 class="border-2 border-dashed border-blue-300 rounded-2xl p-10 text-center transition">

                <!-- Kondisi: belum ada file -->
                <div id="emptyState" class="flex flex-col items-center">
                    <div class="text-6xl mb-4">📄</div>

                    <h2 class="text-2xl font-bold text-gray-700 mb-2">
                        Pilih atau Seret Dokumen
                    </h2>

                    <p class="text-gray-500 mb-1">
                        Format yang didukung: PDF, JPG, JPEG, PNG
                    </p>
                    <p class="text-gray-400 text-sm mb-6">
                        Ukuran maksimal <span id="maxSizeLabel">10</span> MB
                    </p>

                    <input id="file"
                           type="file"
                           name="file"
                           accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                           class="peer sr-only">

                    <label for="file"
                           class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold
                                  px-6 py-3 rounded-xl transition
                                  peer-focus-visible:ring-2 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-blue-500">
                        Pilih File
                    </label>
                </div>

                <!-- Kondisi: file sudah dipilih -->
                <div id="filledState" class="hidden">
                    <div class="flex items-center gap-4 text-left bg-blue-50 border border-blue-100 rounded-xl p-4">

                        <div id="previewBox"
                             class="w-16 h-16 rounded-lg bg-white border border-blue-100 flex items-center justify-center overflow-hidden shrink-0">
                            <span id="previewIcon" class="text-3xl">📄</span>
                            <img id="previewImg" alt="Pratinjau" class="hidden w-full h-full object-cover">
                        </div>

                        <div class="min-w-0 flex-1">
                            <p id="fileName" class="font-semibold text-gray-800 truncate"></p>
                            <p id="fileMeta" class="text-sm text-gray-500"></p>
                        </div>

                        <button type="button" id="removeFile"
                                class="shrink-0 text-gray-400 hover:text-red-600 transition"
                                aria-label="Hapus file">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <button type="button" id="changeFile"
                            class="mt-4 text-sm text-blue-600 hover:underline">
                        Ganti file
                    </button>
                </div>

            </div>

            <!-- Pesan error dari sisi browser -->
            <p id="clientError" class="hidden mt-3 text-sm text-red-600"></p>

            <!-- Button -->
            <div class="mt-8 flex justify-end">
                <button type="submit"
                        id="submitBtn"
                        disabled
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-3
                               rounded-xl font-semibold shadow-lg transition
                               disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600">

                    <svg id="spinner" class="hidden animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>

                    <span id="submitText">Upload Dokumen</span>
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    (function () {
        // Samakan dengan batas di controller (max:10240 = 10 MB)
        const MAX_MB = 10;
        const ALLOWED_EXT = ['pdf', 'jpg', 'jpeg', 'png'];

        const form = document.getElementById('uploadForm');
        const input = document.getElementById('file');
        const dropzone = document.getElementById('dropzone');
        const emptyState = document.getElementById('emptyState');
        const filledState = document.getElementById('filledState');
        const fileName = document.getElementById('fileName');
        const fileMeta = document.getElementById('fileMeta');
        const previewImg = document.getElementById('previewImg');
        const previewIcon = document.getElementById('previewIcon');
        const clientError = document.getElementById('clientError');
        const submitBtn = document.getElementById('submitBtn');
        const spinner = document.getElementById('spinner');
        const submitText = document.getElementById('submitText');

        document.getElementById('maxSizeLabel').textContent = MAX_MB;

        function formatSize(bytes) {
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function showError(msg) {
            clientError.textContent = msg;
            clientError.classList.remove('hidden');
        }

        function clearError() {
            clientError.textContent = '';
            clientError.classList.add('hidden');
        }

        function resetFile() {
            input.value = '';
            if (previewImg.src) URL.revokeObjectURL(previewImg.src);
            previewImg.removeAttribute('src');
            previewImg.classList.add('hidden');
            previewIcon.classList.remove('hidden');
            filledState.classList.add('hidden');
            emptyState.classList.remove('hidden');
            submitBtn.disabled = true;
        }

        function handleFile(file) {
            clearError();
            if (!file) return;

            const ext = file.name.split('.').pop().toLowerCase();

            if (!ALLOWED_EXT.includes(ext)) {
                resetFile();
                showError('Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.');
                return;
            }

            if (file.size > MAX_MB * 1024 * 1024) {
                resetFile();
                showError('Ukuran file ' + formatSize(file.size) + ' melebihi batas ' + MAX_MB + ' MB.');
                return;
            }

            fileName.textContent = file.name;
            fileMeta.textContent = ext.toUpperCase() + ' • ' + formatSize(file.size);

            if (previewImg.src) URL.revokeObjectURL(previewImg.src);

            if (file.type.startsWith('image/')) {
                previewImg.src = URL.createObjectURL(file);
                previewImg.classList.remove('hidden');
                previewIcon.classList.add('hidden');
            } else {
                previewImg.classList.add('hidden');
                previewIcon.classList.remove('hidden');
            }

            emptyState.classList.add('hidden');
            filledState.classList.remove('hidden');
            submitBtn.disabled = false;
        }

        // Pilih lewat dialog
        input.addEventListener('change', () => handleFile(input.files[0]));

        // Hapus / ganti file
        document.getElementById('removeFile').addEventListener('click', () => { clearError(); resetFile(); });
        document.getElementById('changeFile').addEventListener('click', () => input.click());

        // Drag and drop
        ['dragenter', 'dragover'].forEach(evt =>
            dropzone.addEventListener(evt, e => {
                e.preventDefault();
                dropzone.classList.add('border-blue-500', 'bg-blue-50');
            })
        );

        ['dragleave', 'drop'].forEach(evt =>
            dropzone.addEventListener(evt, e => {
                e.preventDefault();
                dropzone.classList.remove('border-blue-500', 'bg-blue-50');
            })
        );

        dropzone.addEventListener('drop', e => {
            const files = e.dataTransfer.files;
            if (!files.length) return;
            input.files = files;
            handleFile(files[0]);
        });

        // Cegah submit ganda + tampilkan loading
        form.addEventListener('submit', () => {
            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            submitText.textContent = 'Mengunggah...';
        });

        // Reset tombol kalau user kembali lewat tombol Back browser
        window.addEventListener('pageshow', e => {
            if (e.persisted) {
                spinner.classList.add('hidden');
                submitText.textContent = 'Upload Dokumen';
                if (input.files.length) handleFile(input.files[0]); else resetFile();
            }
        });
    })();
</script>

@endsection