<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Note;
use Illuminate\Database\Eloquent\Collection;

interface NoteRepository
{
    /**
     * Get notes for a specific noteable entity
     */
    public function getByNoteable(string $noteableType, int $noteableId): Collection;

    /**
     * Find a note by ID
     */
    public function findById(int $id): ?Note;

    /**
     * Create a new note
     */
    public function create(array $data): Note;

    /**
     * Update a note
     */
    public function update(Note $note, array $data): bool;

    /**
     * Delete a note
     */
    public function delete(Note $note): bool;
}
