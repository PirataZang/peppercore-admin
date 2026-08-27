<?php

namespace App\Service;

use App\Models\Client;
use App\Models\Document;
use App\Models\Project;
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
     * Render a document to PDF for download, substituting {{Cliente.*}}/{{Projeto.*}}/{{Valor}}
     * variables (see design.md) with data from the linked client/project, when given.
     *
     * @param array{client_id?: int, project_id?: int, value?: string} $params
     * @return array{pdf: PdfDocument, filename: string}
     */
    public function emit(int $id, array $params = []): array
    {
        $document = $this->index($id);

        if (!$document->active) {
            throw ValidationException::withMessages([
                'active' => ['Somente documentos ativos podem ser emitidos.'],
            ]);
        }

        $client = !empty($params['client_id']) ? Client::find($params['client_id']) : null;
        $project = !empty($params['project_id']) ? Project::find($params['project_id']) : null;

        $variables = $this->documentVariables($client, $project, $params['value'] ?? null);
        $content = $this->replaceVariables($document->content ?? '', $variables);

        $pdf = Pdf::loadHTML($this->toPdfHtml($document, $content))->setPaper('a4');
        $filename = Str::slug($document->name ?: 'documento') . '.pdf';

        return ['pdf' => $pdf, 'filename' => $filename];
    }

    /**
     * Build the {{Cliente.*}} / {{Projeto.*}} / {{Valor}} variable map for a linked
     * client/project (see design.md for the full list and their translated labels).
     */
    private function documentVariables(?Client $client, ?Project $project, ?string $value): array
    {
        $vars = [];

        if ($client) {
            $vars['Cliente.name'] = $client->name;
            $vars['Cliente.email'] = $client->email;
            $vars['Cliente.phone'] = $client->phone;
            $vars['Cliente.address'] = $client->address;
            $vars['Cliente.document'] = $client->document;
            $vars['Cliente.zip_code'] = $client->zip_code;
            $vars['Cliente.street_name'] = $client->street_name;
            $vars['Cliente.street_number'] = $client->street_number;
            $vars['Cliente.neighborhood'] = $client->neighborhood;
            $vars['Cliente.city'] = $client->city;
            $vars['Cliente.state'] = $client->state;
            $vars['Cliente.description'] = $client->description;
        }

        if ($project) {
            $vars['Projeto.name'] = $project->name;
            $vars['Projeto.type'] = ['site' => 'Site', 'sistema' => 'Sistema', 'host' => 'Host'][$project->type] ?? $project->type;
            $vars['Projeto.domain'] = $project->domain;
            $vars['Projeto.client_name'] = $project->client_name;
            $vars['Projeto.client_contact'] = $project->client_contact;
            $vars['Projeto.monthly_value'] = $project->monthly_value !== null
                ? 'R$ ' . number_format((float) $project->monthly_value, 2, ',', '.')
                : null;
            $vars['Projeto.due_day'] = $project->due_day;
            $vars['Projeto.payment_status'] = ['pago' => 'Pago', 'pendente' => 'Pendente', 'atrasado' => 'Atrasado'][$project->payment_status] ?? $project->payment_status;
            $vars['Projeto.description'] = $project->description;
        }

        if ($value !== null && $value !== '') {
            $vars['Valor'] = 'R$ ' . number_format((float) $value, 2, ',', '.');
        }

        return $vars;
    }

    /**
     * Replace every {{Key}} token with its value, HTML-escaped so a variable's raw
     * text (a client's address, say) is always shown literally, never as markup.
     */
    private function replaceVariables(string $html, array $variables): string
    {
        $search = [];
        $replace = [];

        foreach ($variables as $key => $val) {
            $search[] = '{{' . $key . '}}';
            $replace[] = e($val ?? '');
        }

        return $search ? str_replace($search, $replace, $html) : $html;
    }

    /**
     * Wrap the editor's HTML (already variable-substituted) with the print styling
     * used to render it as a PDF. dompdf doesn't resolve the app's CSS custom
     * properties, so colors are hardcoded here.
     */
    private function toPdfHtml(Document $document, string $content): string
    {
        $title = e($document->name);

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
