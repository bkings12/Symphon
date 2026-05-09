<?php

namespace App\Support;

use App\Models\Medicine;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Throwable;

final class MedicineDeletion
{
    public static function attemptDelete(Medicine $record): bool
    {
        if ($summary = $record->deleteBlockerSummary()) {
            self::notifyBlocked($record->name, $summary);

            return false;
        }

        try {
            return $record->delete();
        } catch (QueryException) {
            self::notifyDatabaseBlocked();

            return false;
        }
    }

    /**
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\LazyCollection  $records
     */
    public static function bulkDeleteRecords(DeleteBulkAction $action, $records): void
    {
        foreach ($records as $record) {
            if (! $record instanceof Medicine) {
                continue;
            }

            if ($summary = $record->deleteBlockerSummary()) {
                $action->reportBulkProcessingFailure(
                    'medicine_referenced',
                    fn (int $count): string => $count === 1
                        ? 'Cannot delete: '.$record->name.' is still linked to '.$summary.'.'
                        : "{$count} medicines could not be deleted (still linked to sales, purchases, or prescriptions)."
                );

                continue;
            }

            try {
                $record->delete() || $action->reportBulkProcessingFailure();
            } catch (Throwable $e) {
                $action->reportBulkProcessingFailure();
                report($e);
            }
        }
    }

    private static function notifyBlocked(string $name, string $summary): void
    {
        Notification::make()
            ->title('Cannot delete medicine')
            ->body("{$name} is still linked to {$summary}. Remove or change those records first, or deactivate the product instead.")
            ->danger()
            ->persistent()
            ->send();
    }

    private static function notifyDatabaseBlocked(): void
    {
        Notification::make()
            ->title('Cannot delete medicine')
            ->body('The database refused deletion. This product may still be referenced elsewhere.')
            ->danger()
            ->send();
    }
}
