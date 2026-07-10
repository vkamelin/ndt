<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class NotificationTemplate extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'title',
        'subject',
        'body',
        'channels',
        'meta',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'channels' => 'array',
        'meta' => 'array',
        'is_active' => 'bool',
    ];

    /**
     * @param  array<string, scalar|null>  $context
     * @return array{title: string, subject: string, body: string}
     */
    public function render(array $context): array
    {
        return [
            'title' => $this->replacePlaceholders($this->title, $context),
            'subject' => $this->replacePlaceholders($this->subject, $context),
            'body' => $this->replacePlaceholders($this->body, $context),
        ];
    }

    /**
     * @param  array<string, scalar|null>  $context
     */
    private function replacePlaceholders(string $value, array $context): string
    {
        $replacements = [];

        foreach ($context as $key => $contextValue) {
            $replacements['{{'.$key.'}}'] = (string) ($contextValue ?? '');
        }

        return trim((string) strtr($value, $replacements));
    }
}
