<?php

declare(strict_types=1);

namespace App\Modules\Documents\Models;

use App\Modules\Admin\Models\AbstractDictionary;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class DocumentType extends AbstractDictionary
{
    protected $table = 'document_types';

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'document_type_id');
    }
}
