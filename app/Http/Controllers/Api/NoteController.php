<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NoteCollection;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request): NoteCollection
    {
        $validated = $request->validate([
            'noteable_type' => ['required', 'string'],
            'noteable_id' => ['required', 'integer'],
        ]);

        $notes = Note::query()
            ->where('noteable_type', $validated['noteable_type'])
            ->where('noteable_id', $validated['noteable_id'])
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return new NoteCollection($notes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'noteable_type' => ['required', 'string'],
            'noteable_id' => ['required', 'integer'],
            'body' => ['required', 'string'],
        ]);

        $user = auth()->user();

        $note = Note::create([
            'user_id' => $user->id,
            'company_id' => $user->current_company_id,
            'noteable_type' => $validated['noteable_type'],
            'noteable_id' => $validated['noteable_id'],
            'body' => $validated['body'],
        ]);

        return (new NoteResource($note->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Note $note): JsonResponse
    {
        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }
}
