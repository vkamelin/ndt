<?php

declare(strict_types=1);

namespace App\Modules\Admin\Support;

use App\Modules\Admin\Models\ActType;
use App\Modules\Admin\Models\AbstractDictionary;
use App\Modules\Admin\Models\Drawing;
use App\Modules\Admin\Models\ChemicalType;
use App\Modules\Admin\Models\DefectType;
use App\Modules\Admin\Models\FilmType;
use App\Modules\Admin\Models\Line;
use App\Modules\Admin\Models\Material;
use App\Modules\Admin\Models\Medium;
use App\Modules\Admin\Models\NormativeDocument;
use App\Modules\Admin\Models\PipelineCategory;
use App\Modules\Admin\Models\RegisterType;
use App\Modules\Admin\Models\ResultStatus;
use App\Modules\Admin\Models\Title;
use App\Modules\Admin\Models\WeldType;
use App\Modules\Admin\Models\WeldingProcess;

final class DictionaryRegistry
{
    /**
     * @return array<string, array{label: string, model: class-string<AbstractDictionary>}>
     */
    public static function definitions(): array
    {
        return [
            'materials' => [
                'label' => 'Материалы',
                'model' => Material::class,
            ],
            'welding-processes' => [
                'label' => 'Сварочные процессы',
                'model' => WeldingProcess::class,
            ],
            'weld-types' => [
                'label' => 'Типы сварных соединений',
                'model' => WeldType::class,
            ],
            'titles' => [
                'label' => 'Титулы',
                'model' => Title::class,
            ],
            'drawings' => [
                'label' => 'Чертежи',
                'model' => Drawing::class,
            ],
            'lines' => [
                'label' => 'Линии',
                'model' => Line::class,
            ],
            'pipeline-categories' => [
                'label' => 'Категории трубопроводов',
                'model' => PipelineCategory::class,
            ],
            'media' => [
                'label' => 'Среды',
                'model' => Medium::class,
            ],
            'normative-documents' => [
                'label' => 'НТД',
                'model' => NormativeDocument::class,
            ],
            'defect-types' => [
                'label' => 'Типы дефектов',
                'model' => DefectType::class,
            ],
            'result-statuses' => [
                'label' => 'Статусы результатов',
                'model' => ResultStatus::class,
            ],
            'register-types' => [
                'label' => 'Типы реестров',
                'model' => RegisterType::class,
            ],
            'act-types' => [
                'label' => 'Типы актов',
                'model' => ActType::class,
            ],
            'film-types' => [
                'label' => 'Типы пленок',
                'model' => FilmType::class,
            ],
            'chemical-types' => [
                'label' => 'Типы химии',
                'model' => ChemicalType::class,
            ],
        ];
    }

    /**
     * @return array{label: string, model: class-string<AbstractDictionary>}
     */
    public static function definition(string $dictionary): array
    {
        $definitions = self::definitions();

        abort_unless(isset($definitions[$dictionary]), 404);

        return $definitions[$dictionary];
    }
}
