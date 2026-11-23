<?php

namespace App\Enums;

enum LicenseApplicationStatus: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case UNDER_REVIEW = 'UNDER_REVIEW';
    case INSPECTION_SCHEDULED = 'INSPECTION_SCHEDULED';
    case INSPECTION_COMPLETED = 'INSPECTION_COMPLETED';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case EXPIRED = 'EXPIRED';
    case RENEWED = 'RENEWED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::UNDER_REVIEW => 'Under Review',
            self::INSPECTION_SCHEDULED => 'Inspection Scheduled',
            self::INSPECTION_COMPLETED => 'Inspection Completed',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::EXPIRED => 'Expired',
            self::RENEWED => 'Renewed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-slate-100 text-slate-700',
            self::SUBMITTED => 'bg-sky-100 text-sky-800',
            self::UNDER_REVIEW => 'bg-indigo-100 text-indigo-800',
            self::INSPECTION_SCHEDULED => 'bg-amber-100 text-amber-800',
            self::INSPECTION_COMPLETED => 'bg-teal-100 text-teal-800',
            self::APPROVED => 'bg-emerald-100 text-emerald-800',
            self::REJECTED => 'bg-rose-100 text-rose-800',
            self::EXPIRED => 'bg-orange-100 text-orange-800',
            self::RENEWED => 'bg-purple-100 text-purple-800',
        };
    }

    /**
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT => [self::SUBMITTED],
            self::SUBMITTED => [self::UNDER_REVIEW, self::REJECTED],
            self::UNDER_REVIEW => [self::INSPECTION_SCHEDULED, self::REJECTED],
            self::INSPECTION_SCHEDULED => [self::INSPECTION_COMPLETED],
            self::INSPECTION_COMPLETED => [self::APPROVED, self::REJECTED],
            self::APPROVED => [self::EXPIRED, self::RENEWED],
            self::EXPIRED => [self::RENEWED],
            self::REJECTED, self::RENEWED => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
