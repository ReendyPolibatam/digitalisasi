<h2>Verifikasi Dokumen</h2>

@if(session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

@if(session('error'))
    <p style="color: red;">{{ session('error') }}</p>
@endif

<table border="1" cellpadding="8">
    <tr>
        <th>Nama File</th>
        <th>Preview</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>

    @forelse($documents as $doc)
    <tr>
        <td>{{ $doc->file_name }}</td>

        <td>
            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank">
                Lihat
            </a>
        </td>

        <td>
            @if($doc->status == 'pending')
                <span style="color: orange;">Pending</span>
            @elseif($doc->status == 'approved')
                <span style="color: green;">Approved</span>
            @elseif($doc->status == 'rejected')
                <span style="color: red;">Rejected</span>
            @endif
        </td>

        <td>
            @if($doc->status == 'pending')
                <a href="{{ route('documents.approve', $doc->id) }}">Approve</a>
                |
                <a href="{{ route('documents.reject', $doc->id) }}">Reject</a>
            @else
                <span>Tidak ada aksi</span>
            @endif
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="4">Belum ada dokumen</td>
    </tr>
    @endforelse
</table>