<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any notes.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific note.
     * Employees can only view their own notes; owners can view all notes.
     */
    public function view(User $user, Note $note): bool
    {
        return $user->isOwner() || $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can create notes.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the note.
     * Employees can only update their own notes; owners can update all notes.
     */
    public function update(User $user, Note $note): bool
    {
        return $user->isOwner() || $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can complete the note.
     */
    public function complete(User $user, Note $note): bool
    {
        return $user->isOwner() || $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can cancel the note.
     */
    public function cancel(User $user, Note $note): bool
    {
        return $user->isOwner() || $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete / archive the note.
     */
    public function delete(User $user, Note $note): bool
    {
        return $user->isOwner() || $note->user_id === $user->id;
    }
}

