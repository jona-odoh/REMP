<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('documents.view');
    }

    public function view(User $user, Document $document): bool
    {
        return $user->can('documents.view');
    }

    public function create(User $user): bool
    {
        return $user->can('documents.create');
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->can('documents.delete') || $document->uploaded_by === $user->getKey();
    }
}
