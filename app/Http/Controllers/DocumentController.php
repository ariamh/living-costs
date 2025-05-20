<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::latest()->get();
        return view('documents.index', compact('documents'));
    }

public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,pdf|max:2048',
        ]);

        $file = $request->file('file');

        $storedName = Str::uuid() . '.' . $file->extension();
        // $path = $file->storeAs('documents', $storedName); // storage/app/documents
        $path = $file->move(public_path('uploads'), $file->getClientOriginalName());

        $document = Document::create([
            'original_name' => $file->getClientOriginalName(),
            'stored_name'   => $storedName,
            // 'mime_type'     => $file->getMimeType(),
            // 'size'          => $file->getSize(),
        ]);

        return redirect()->back()->with('success', 'File berhasil diupload.');
    }

    public function download(Document $document)
    {
        return Storage::download('documents/' . $document->stored_name, $document->original_name);
    }

    public function show($filename)
    {
        $path = 'documents/' . $filename;

        if (!Storage::exists($path)) {
            abort(404, 'Gambar tidak ditemukan.');
        }

        // Validasi MIME hanya untuk image
        $mime = Storage::mimeType($path);
        if (!Str::startsWith($mime, 'documents/')) {
            abort(403, 'File bukan gambar.');
        }

        return response()->file(storage_path('app/' . $path), [
            'Content-Type' => $mime,
        ]);
    }
}
