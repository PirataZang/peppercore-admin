<?php

namespace App\Service;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentService
{
    /**
     * Get a paginated list of documents.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Document::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['active'])) {
            $query->where('active', filter_var($filters['active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->orderByDesc('updated_at')->paginate($perPage);
    }

    /**
     * Get a single document by ID.
     */
    public function index(int $id): Document
    {
        return Document::findOrFail($id);
    }

    /**
     * Create a new document.
     */
    public function create(array $data): Document
    {
        $data['user_id'] = Auth::id();

        if (array_key_exists('content', $data)) {
            $data['content'] = $this->sanitizeHtml($data['content'] ?? '');
        }

        return Document::create($data);
    }

    /**
     * Update an existing document.
     */
    public function update(int $id, array $data): Document
    {
        $document = Document::findOrFail($id);

        if (array_key_exists('content', $data)) {
            $data['content'] = $this->sanitizeHtml($data['content'] ?? '');
        }

        $document->update($data);
        return $document;
    }

    /**
     * Strip anything the editor's toolbar can't itself produce (script tags, event
     * handler attributes, etc.) before content is persisted — it's later replayed
     * both into the browser via v-html and into dompdf, so this is the one place
     * that has to hold the line against injected HTML/JS.
     */
    private function sanitizeHtml(?string $html): string
    {
        if (!$html) {
            return '';
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set(
            'HTML.Allowed',
            'p,h1,h2,h3,blockquote,b,strong,i,em,u,s,strike,ul,ol,li,hr,br,' .
            'a[href],img[src],span[style],font[color],div[style|align]'
        );
        $config->set('CSS.AllowedProperties', ['color', 'background-color', 'text-align']);
        // data: is needed for images pasted straight into the editor (stored as base64).
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'data' => true]);

        return (new HTMLPurifier($config))->purify($html);
    }

    /**
     * Delete a document.
     */
    public function delete(int $id): bool
    {
        $document = Document::findOrFail($id);
        return $document->delete();
    }

    /**
     * Render a document to PDF for download.
     *
     * @return array{pdf: PdfDocument, filename: string}
     */
    public function emit(int $id): array
    {
        $document = $this->index($id);

        if (!$document->active) {
            throw ValidationException::withMessages([
                'active' => ['Somente documentos ativos podem ser emitidos.'],
            ]);
        }

        $pdf = Pdf::loadHTML($this->toPdfHtml($document))->setPaper('a4');
        $filename = Str::slug($document->name ?: 'documento') . '.pdf';

        return ['pdf' => $pdf, 'filename' => $filename];
    }

    /**
     * Wrap the editor's stored HTML with the print styling used to render it as a PDF.
     * dompdf doesn't resolve the app's CSS custom properties, so colors are hardcoded here.
     */
    private function toPdfHtml(Document $document): string
    {
        $title = e($document->name);
        $content = $document->content ?? '';

        return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>{$title}</title>
                <style>
                    body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; color: #0f172a; line-height: 1.6; }
                    h1 { font-size: 22px; font-weight: 700; margin: 0 0 12px; }
                    h2 { font-size: 18px; font-weight: 700; margin: 0 0 10px; }
                    h3 { font-size: 15px; font-weight: 700; margin: 0 0 8px; }
                    p { margin: 0 0 12px; }
                    blockquote { margin: 0 0 12px; padding-left: 16px; border-left: 3px solid #cbd5e1; color: #475569; }
                    a { color: #4f46e5; }
                </style>
            </head>
            <body>{$content}</body>
            </html>
            HTML;
    }
}
