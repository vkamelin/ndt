<?php

declare(strict_types=1);

namespace App\Modules\Registers\Enums;

enum TransferRegisterStatus: string
{
    case Draft = 'draft';
    case Formed = 'formed';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Returned = 'returned';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::Formed => 'Сформирован',
            self::Sent => 'Передан',
            self::Accepted => 'Принят',
            self::Returned => 'Возвращен',
            self::Closed => 'Закрыт',
        };
    }

    public function canBeEdited(): bool
    {
        return in_array($this, [self::Draft, self::Returned], true);
    }

    public function canBeFormed(): bool
    {
        return in_array($this, [self::Draft, self::Returned], true);
    }

    public function canBeSent(): bool
    {
        return in_array($this, [self::Formed, self::Returned], true);
    }

    public function canBeAccepted(): bool
    {
        return in_array($this, [self::Sent, self::Returned], true);
    }

    public function canBeReturned(): bool
    {
        return in_array($this, [self::Formed, self::Sent, self::Accepted], true);
    }

    public function canBeClosed(): bool
    {
        return in_array($this, [self::Accepted, self::Returned], true);
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
