<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Smalot\PdfParser\Parser;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $documents = $request->user()
            ->documents()
            ->withCount('quizzes')
            ->latest()
            ->paginate(10);

        return view('documents.index', compact('documents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,txt', 'max:10240'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            if ($extension === 'pdf') {
                $text = (new Parser)->parseFile($file->getRealPath())->getText();
            } else {
                $text = file_get_contents($file->getRealPath());
            }
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'file' => 'Failed to read document contents. Ensure the file is not corrupted or password protected.',
            ]);
        }

        if (mb_strlen(trim($text)) < 50) {
            return back()->withInput()->withErrors([
                'file' => 'The uploaded file does not contain enough readable text (minimum 50 characters required). Scanned image PDFs without OCR are not supported.',
            ]);
        }

        $path = $file->store('documents', 'local');

        $request->user()->documents()->create([
            'title' => $request->input('title') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType() ?: 'application/pdf',
            'extracted_text' => $text,
        ]);

        return redirect()->route('documents.index')->with('status', 'PDF document stored successfully! You can now generate quizzes from it.');
    }

    public function download(Request $request, Document $document): StreamedResponse
    {
        abort_if($document->user_id !== $request->user()->id, 403, 'Unauthorized access to this document.');

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'Stored file not found.');
        }

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        abort_if($document->user_id !== $request->user()->id, 403, 'Unauthorized access to this document.');

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('status', 'Document deleted successfully.');
    }
}
