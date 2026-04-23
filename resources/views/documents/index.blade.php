<h2>Daftar Dokumen</h2>

<a href="{{ route('documents.create') }}">Upload Dokumen</a>

<table border="1">
    <tr>
        <th>Nama File</th>
        <th>Status</th>
    </tr>

    @foreach($documents as $doc)
    <tr>
        <td>{{ $doc->file_name }}</td>
        <td>{{ $doc->status }}</td>
    </tr>
    @endforeach
</table>