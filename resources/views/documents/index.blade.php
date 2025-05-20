<!-- resources/views/documents/index.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Upload Dokumen</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.0.24/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">

    <h1 class="text-xl font-bold mb-4">Upload Dokumen</h1>

    @if (session('success'))
        <div class="bg-green-100 p-2 mb-3 rounded text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="mb-5">
        @csrf
        <input type="file" name="file" class="border p-2">
        @error('file')
            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
        @enderror
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded ml-2">Upload</button>
    </form>

    <h2 class="font-semibold text-lg mb-2">Daftar File</h2>
    <ul class="bg-white shadow rounded p-4">
        @forelse ($documents as $doc)
            <li class="mb-2">
                📄 {{ $doc->original_name }} ({{ number_format($doc->size / 1024, 2) }} KB)
                <img src="{{ route('documents.show', $doc->stored_name) }}" alt="Gambar" class="w-64 rounded shadow">
                <a href="{{ route('documents.download', $doc) }}" class="text-blue-500 hover:underline ml-2">
                    Download
                </a>
            </li>
        @empty
            <li>Tidak ada file.</li>
        @endforelse
    </ul>

</body>
</html>
