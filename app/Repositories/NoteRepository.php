<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Note;
use App\Repositories\Interfaces\NoteRepository as NoteRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

class NoteRepository implements NoteRepositoryContract
{
    /**
     * Get notes for a specific noteable entity
     */
    public function getByNoteable(string $noteableType, int $noteableId): Collection
    {
        return Note::query()
            ->where('noteable_type', $noteableType)
            ->where('noteable_id', $noteableId)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Find a note by ID
     */
    public function findById(int $id): ?Note
    {
        return Note::find($id);
    }

    /**
     * Create a new note
     */
    public function create(array $data): Note
    {
        return Note::create($data);
    }

    /**
     * Update a note
     */
    public function update(Note $note, array $data): bool
    {
        return $note->update($data);
    }

    /**
     * Delete a note
     */
    public function delete(Note $note): bool
    {
        return $note->delete();
    }
}
