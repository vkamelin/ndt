<?php

declare(strict_types=1);

namespace App\Modules\Welds\Models;

use App\Modules\Admin\Models\Drawing;
use App\Modules\Admin\Models\Line;
use App\Modules\Admin\Models\Material;
use App\Modules\Admin\Models\Medium;
use App\Modules\Admin\Models\NormativeDocument;
use App\Modules\Admin\Models\PipelineCategory;
use App\Modules\Admin\Models\Title;
use App\Modules\Admin\Models\WeldType;
use App\Modules\Admin\Models\WeldingProcess;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\Objects\Models\NdtObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Weld extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'object_id',
        'title_id',
        'drawing_id',
        'line_id',
        'weld_number',
        'diameter',
        'thickness',
        'material_1_id',
        'material_2_id',
        'welded_at',
        'welding_process_id',
        'weld_type_id',
        'pipeline_category_id',
        'medium_id',
        'pwht',
        'normative_document_id',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'diameter' => 'decimal:2',
        'thickness' => 'decimal:2',
        'welded_at' => 'date',
        'pwht' => 'bool',
        'status' => \App\Modules\Welds\Enums\WeldStatus::class,
        'deleted_at' => 'datetime',
    ];

    public function object(): BelongsTo
    {
        return $this->belongsTo(NdtObject::class, 'object_id');
    }

    public function title(): BelongsTo
    {
        return $this->belongsTo(Title::class);
    }

    public function drawing(): BelongsTo
    {
        return $this->belongsTo(Drawing::class);
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(Line::class);
    }

    public function material1(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_1_id');
    }

    public function material2(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_2_id');
    }

    public function weldingProcess(): BelongsTo
    {
        return $this->belongsTo(WeldingProcess::class);
    }

    public function weldType(): BelongsTo
    {
        return $this->belongsTo(WeldType::class);
    }

    public function pipelineCategory(): BelongsTo
    {
        return $this->belongsTo(PipelineCategory::class);
    }

    public function medium(): BelongsTo
    {
        return $this->belongsTo(Medium::class);
    }

    public function normativeDocument(): BelongsTo
    {
        return $this->belongsTo(NormativeDocument::class);
    }

    public function requests(): BelongsToMany
    {
        return $this->belongsToMany(NdtRequest::class, 'ndt_request_items')->withTimestamps();
    }

    public function ndtMethods(): BelongsToMany
    {
        return $this->belongsToMany(NdtMethod::class, 'weld_ndt_methods')->withTimestamps();
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(WeldStatusHistory::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(NdtResult::class);
    }
}
