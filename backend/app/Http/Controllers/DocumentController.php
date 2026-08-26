<?php

namespace App\Http\Controllers;

use App\Service\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Display a listing of documents.
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'active']);
        $perPage = (int) $request->input('per_page', 15);

        $documents = $this->documentService->list($filters, $perPage);

        return response()->json($documents);
    }

    /**
     * Display the specified document.
     */
    public function index(int $id): JsonResponse
    {
        $document = $this->documentService->index($id);

        return response()->json($document);
    }

    /**
     * Store a newly created document.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $document = $this->documentService->create($validated);

        return response()->json($document, 201);
    }

    /**
     * Update the specified document.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate($this->rules(sometimes: true));

        $document = $this->documentService->update($id, $validated);

        return response()->json($document);
    }

    /**
     * Remove the specified document.
     */
    public function delete(int $id): JsonResponse
    {
        $this->documentService->delete($id);

        return response()->json(['message' => 'Document deleted successfully']);
    }

    /**
     * Emit the specified document as a downloadable PDF.
     */
    public function emit(int $id)
    {
        ['pdf' => $pdf, 'filename' => $filename] = $this->documentService->emit($id);

        return $pdf->download($filename);
    }

    private function rules(bool $sometimes = false): array
    {
        $required = $sometimes ? 'sometimes' : 'required';

        return [
            'name' => "{$required}|string|max:255",
            'subject' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string|max:25',
            'active' => 'sometimes|boolean',
            'content' => 'sometimes|nullable|string',
        ];
    }
}
