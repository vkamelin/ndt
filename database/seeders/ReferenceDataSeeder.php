<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Admin\Models\AbstractDictionary;
use App\Modules\Admin\Models\ChemicalType;
use App\Modules\Admin\Models\DefectType;
use App\Modules\Admin\Models\Drawing;
use App\Modules\Admin\Models\FilmType;
use App\Modules\Admin\Models\Line;
use App\Modules\Admin\Models\Material;
use App\Modules\Admin\Models\Medium;
use App\Modules\Admin\Models\NormativeDocument;
use App\Modules\Admin\Models\PipelineCategory;
use App\Modules\Admin\Models\ResultStatus;
use App\Modules\Admin\Models\Title;
use App\Modules\Admin\Models\WeldingProcess;
use App\Modules\Admin\Models\WeldType;
use App\Modules\Employees\Models\Position;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use Illuminate\Database\Seeder;

final class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCities();
        $this->seedObjects();
        $this->seedPositions();
        $this->seedDictionaries();
    }

    private function seedCities(): void
    {
        foreach ([
            'Орск',
            'Комсомольск-на-Амуре',
        ] as $name) {
            City::query()->updateOrCreate(
                ['name' => $name],
                ['is_active' => true, 'comment' => null],
            );
        }
    }

    private function seedObjects(): void
    {
        $cities = City::query()->whereIn('name', [
            'Орск',
            'Комсомольск-на-Амуре',
        ])->get()->keyBy('name');

        $objects = [
            [
                'city' => 'Орск',
                'name' => 'Орский участок',
                'code' => 'ORSK-01',
            ],
            [
                'city' => 'Комсомольск-на-Амуре',
                'name' => 'Комсомольский участок',
                'code' => 'KNA-01',
            ],
        ];

        foreach ($objects as $object) {
            NdtObject::query()->updateOrCreate(
                [
                    'city_id' => $cities[$object['city']]->id,
                    'name' => $object['name'],
                ],
                [
                    'organization_id' => null,
                    'code' => $object['code'],
                    'is_active' => true,
                    'comment' => null,
                ],
            );
        }
    }

    private function seedPositions(): void
    {
        foreach ([
            'Администратор системы',
            'Начальник участка',
            'Инженер НК / Дешифровщик',
            'Дефектоскопист',
            'Лаборант',
        ] as $name) {
            Position::query()->updateOrCreate(
                ['name' => $name],
                ['is_active' => true, 'comment' => null],
            );
        }
    }

    private function seedDictionaries(): void
    {
        $dictionaries = [
            Material::class => [
                'Сталь 20',
                '09Г2С',
                '12Х18Н10Т',
            ],
            WeldingProcess::class => [
                'РД',
                'РАД',
                'МП',
                'УП',
            ],
            WeldType::class => [
                'Стыковое',
                'Угловое',
                'Тавровое',
            ],
            Title::class => [
                'Титул 1',
                'Титул 2',
            ],
            Drawing::class => [
                'Чертеж 1',
                'Чертеж 2',
            ],
            Line::class => [
                'Линия 1',
                'Линия 2',
            ],
            PipelineCategory::class => [
                'Категория I',
                'Категория II',
                'Категория III',
            ],
            Medium::class => [
                'Вода',
                'Нефть',
                'Газ',
            ],
            NormativeDocument::class => [
                'ГОСТ 16037-80',
                'ГОСТ 8713-79',
                'РД 03-606-03',
            ],
            DefectType::class => [
                'Трещина',
                'Поры',
                'Непровар',
            ],
            ResultStatus::class => [
                'Положительный',
                'Отрицательный',
                'Требует уточнения',
            ],
            FilmType::class => [
                'Рентгеновская пленка',
                'Цифровой снимок',
            ],
            ChemicalType::class => [
                'Проявитель',
                'Фиксаж',
                'Стабилизатор',
            ],
        ];

        foreach ($dictionaries as $modelClass => $names) {
            $this->seedDictionary($modelClass, $names);
        }
    }

    /**
     * @param class-string<AbstractDictionary> $modelClass
     * @param list<string> $names
     */
    private function seedDictionary(string $modelClass, array $names): void
    {
        foreach ($names as $name) {
            $modelClass::query()->updateOrCreate(
                ['name' => $name],
                ['is_active' => true, 'comment' => null],
            );
        }
    }
}
