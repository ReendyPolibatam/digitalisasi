<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    // 🔥 ADMIN - APPROVE
    public function approve($id)
    {
        $doc = Document::findOrFail($id);

        if ($doc->status != 'pending') {
            return back()->with('error', 'Dokumen sudah diproses');
        }

        $doc->status = 'approved';
        $doc->save();

        return back()->with('success', 'Dokumen berhasil di-approve');
    }

    // 🔥 ADMIN - REJECT
    public function reject($id)
    {
        $doc = Document::findOrFail($id);

        if ($doc->status != 'pending') {
            return back()->with('error', 'Dokumen sudah diproses');
        }

        $doc->status = 'rejected';
        $doc->save();

        return back()->with('success', 'Dokumen berhasil di-reject');
    }

    // 🔥 ADMIN - LIHAT SEMUA DOKUMEN
    public function adminIndex()
    {
        $documents = Document::latest()->get();
        return view('admin.documents', compact('documents'));
    }

    // 🔥 DOWNLOAD FILE
    public function download($id)
    {
        $doc = Document::findOrFail($id);

        $path = storage_path('app/public/' . $doc->file_path);

        if (!file_exists($path)) {
            return back()->with('error', 'File tidak ditemukan');
        }

        return response()->download($path, $doc->file_name);
    }

    // 🔥 STAFF - LIHAT DOKUMEN SENDIRI
    public function index()
    {
        $documents = Document::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('documents.index', compact('documents'));
    }

    // 🔥 FORM UPLOAD
    public function create()
    {
        return view('documents.create');
    }

    // 🔥 SIMPAN DOKUMEN
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        Document::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'pending', // 🔥 penting!
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil diupload');
    }
}