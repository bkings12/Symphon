<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StockBatch;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockBatchPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StockBatch');
    }

    public function view(AuthUser $authUser, StockBatch $stockBatch): bool
    {
        return $authUser->can('View:StockBatch');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StockBatch');
    }

    public function update(AuthUser $authUser, StockBatch $stockBatch): bool
    {
        return $authUser->can('Update:StockBatch');
    }

    public function delete(AuthUser $authUser, StockBatch $stockBatch): bool
    {
        return $authUser->can('Delete:StockBatch');
    }

    public function restore(AuthUser $authUser, StockBatch $stockBatch): bool
    {
        return $authUser->can('Restore:StockBatch');
    }

    public function forceDelete(AuthUser $authUser, StockBatch $stockBatch): bool
    {
        return $authUser->can('ForceDelete:StockBatch');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StockBatch');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StockBatch');
    }

    public function replicate(AuthUser $authUser, StockBatch $stockBatch): bool
    {
        return $authUser->can('Replicate:StockBatch');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StockBatch');
    }

}