<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Enums;

enum ConclusionStatus: string
{
    case Draft = 'draft';
    case Prepared = 'prepared';
    case OnCheck = 'on_check';
    case Returned = 'returned';
    case Approved = 'approved';
    case Issued = 'issued';
    case Annulled = 'annulled';
    case Replaced = 'replaced';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::Prepared => 'Подготовлено',
            self::OnCheck => 'На проверке',
            self::Returned => 'Возвращено на доработку',
            self::Approved => 'Утверждено',
            self::Issued => 'Выдано',
            self::Annulled => 'Аннулировано',
            self::Replaced => 'Заменено',
        };
    }

    public function canBeEdited(): bool
    {
        return in_array($this, [self::Draft, self::Prepared, self::Returned], true);
    }

    public function canBeSubmitted(): bool
    {
        return in_array($this, [self::Draft, self::Prepared, self::Returned], true);
    }

    public function canBeApproved(): bool
    {
        return $this === self::OnCheck;
    }

    public function canBeIssued(): bool
    {
        return $this === self::Approved;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
