<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoteIndexRequest;
use App\Http\Requests\NoteStoreRequest;
use App\Http\Resources\NoteCollection;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use App\Repositories\Contracts\NoteRepository;
use Illuminate\Http\JsonResponse;

class NoteController extends Controller
{
    public function __construct(
        private readonly NoteRepository $noteRepository
    ) {}

    public function index(NoteIndexRequest $request): NoteCollection
    {
        $notes = $this->noteRepository->getByNoteable(
            $request->getNoteableType(),
            $request->getNoteableId()
        );

        return new NoteCollection($notes);
    }

    public function store(NoteStoreRequest $request): JsonResponse
    {
        $user = auth()->user();

        $note = $this->noteRepository->create([
            'user_id' => $user->id,
            'company_id' => $user->current_company_id,
            'noteable_type' => $request->getNoteableType(),
            'noteable_id' => $request->getNoteableId(),
            'body' => $request->getBody(),
        ]);

        return new NoteResource($note->load('user'))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Note $note): JsonResponse
    {
        $this->noteRepository->delete($note);

        return response()->json(['message' => 'Note deleted successfully']);
    }
}
