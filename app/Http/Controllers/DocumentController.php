<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | STAFF
    |--------------------------------------------------------------------------
    */

    // Dashboard Staff
    public function staffDashboard()
    {
        $userId = Auth::id();

        $total = Document::where('user_id', $userId)->count();

        $pending = Document::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $approved = Document::where('user_id', $userId)
            ->where('status', 'approved')
            ->count();

        $rejected = Document::where('user_id', $userId)
            ->where('status', 'rejected')
            ->count();

        $documents = Document::where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {

            $monthlyData[] = Document::where('user_id', $userId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', $month)
                ->count();
        }

        return view('staff.dashboard', compact(
            'total',
            'pending',
            'approved',
            'rejected',
            'documents',
            'monthlyData'
        ));
    }

    // Dokumen Saya
    public function index()
    {
        $documents = Document::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('staff.documents', compact('documents'));
    }

    // Halaman Upload
    public function create()
    {
        return view('staff.upload');
    }

    // Simpan Dokumen
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $file = $request->file('file');

        $path = $file->store('documents', 'public');

        Document::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status'    => 'pending',
            'user_id'   => Auth::id(),
        ]);

        return redirect()
            ->route('staff.dashboard')
            ->with(
                'success',
                'Dokumen berhasil diupload'
            );
    }

    // Download Dokumen
    public function download($id)
    {
        $doc = Document::findOrFail($id);

        if (
            Auth::user()->role === 'staff' &&
            $doc->user_id != Auth::id()
        ) {
            abort(403);
        }

        $path = storage_path(
            'app/public/' . $doc->file_path
        );

        if (!file_exists($path)) {
            return back()->with(
                'error',
                'File tidak ditemukan'
            );
        }

        return response()->download(
            $path,
            $doc->file_name
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    // Dashboard Admin
    public function adminDashboard()
    {
        $total = Document::count();

        $pending = Document::where(
            'status',
            'pending'
        )->count();

        $approved = Document::where(
            'status',
            'approved'
        )->count();

        $rejected = Document::where(
            'status',
            'rejected'
        )->count();

        $documents = Document::latest()
            ->take(5)
            ->get();

        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {

            $monthlyData[] = Document::whereYear(
                'created_at',
                now()->year
            )
            ->whereMonth(
                'created_at',
                $month
            )
            ->count();
        }

        return view(
            'admin.dashboard',
            compact(
                'total',
                'pending',
                'approved',
                'rejected',
                'documents',
                'monthlyData'
            )
        );
    }

    // Verifikasi Dokumen
    public function adminIndex()
    {
        $documents = Document::latest()
            ->paginate(10);

        return view(
            'admin.documents',
            compact('documents')
        );
    }

    // Library Dokumen
    public function library()
    {
        /*
        |--------------------------------------------------------------------------
        | Dummy Data
        | Nanti diganti hasil OCR + Rule Based
        |--------------------------------------------------------------------------
        */

        $ships = collect([
            (object) [
                'ship_name' => 'MT RISKITA',
                'voyage' => '010/RISKITA/PL/B40/V/2026',
                'total_documents' => 3
            ],
            (object) [
                'ship_name' => 'MT MERATUS',
                'voyage' => '011/MERATUS/PL/B41/V/2026',
                'total_documents' => 3
            ],
            (object) [
                'ship_name' => 'SPOB ANUGERAH',
                'voyage' => '012/ANUGERAH/PL/B42/V/2026',
                'total_documents' => 3
            ]
        ]);

        return view(
            'admin.library',
            compact('ships')
        );
    }

    // Approve Dokumen
    public function approve($id)
    {
        $doc = Document::findOrFail($id);

        if ($doc->status !== 'pending') {

            return redirect()
                ->route('admin.documents')
                ->with(
                    'error',
                    'Dokumen sudah diproses'
                );
        }

        $doc->update([
            'status' => 'approved'
        ]);

        return redirect()
            ->route('admin.documents')
            ->with(
                'success',
                'Dokumen berhasil di-approve'
            );
    }

    // Reject Dokumen
    public function reject($id)
    {
        $doc = Document::findOrFail($id);

        if ($doc->status !== 'pending') {

            return redirect()
                ->route('admin.documents')
                ->with(
                    'error',
                    'Dokumen sudah diproses'
                );
        }

        $doc->update([
            'status' => 'rejected'
        ]);

        return redirect()
            ->route('admin.documents')
            ->with(
                'success',
                'Dokumen berhasil di-reject'
            );
    }

    public function showDocument($id)
{
        $document = Document::findOrFail($id);

        return view(
            'admin.document-detail',
            compact('document')
        );
}

    // Monitoring
    public function monitoring()
    {
        $documents = Document::latest()
            ->paginate(10);

        return view(
            'admin.monitoring',
            compact('documents')
        );
    }
}