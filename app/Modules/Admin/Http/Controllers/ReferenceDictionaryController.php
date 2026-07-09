<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Http\Requests\StoreDictionaryEntryRequest;
use App\Modules\Admin\Http\Requests\UpdateDictionaryEntryRequest;
use App\Modules\Admin\Models\AbstractDictionary;
use App\Modules\Admin\Services\ReferenceDictionaryService;
use App\Modules\Admin\Support\DictionaryRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ReferenceDictionaryController extends Controller
{
    public function overview(): View
    {
        $this->authorize('directories.manage');

        return view('modules.admin.dictionaries.overview', [
            'definitions' => DictionaryRegistry::definitions(),
        ]);
    }

    public function index(Request $request, string $dictionary): View
    {
        $this->authorize('directories.manage');

        $definition = DictionaryRegistry::definition($dictionary);
        $modelClass = $definition['model'];

        /** @var class-string<AbstractDictionary> $modelClass */
        $entries = $modelClass::query()
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $query->where('name', 'like', '%'.$request->string('search')->toString().'%');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('modules.admin.dictionaries.index', [
            'dictionary' => $dictionary,
            'definition' => $definition,
            'entries' => $entries,
        ]);
    }

    public function store(StoreDictionaryEntryRequest $request, string $dictionary, ReferenceDictionaryService $dictionaries): RedirectResponse
    {
        $this->authorize('directories.manage');

        $definition = DictionaryRegistry::definition($dictionary);

        $dictionaries->create(
            modelClass: $definition['model'],
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Элемент справочника создан.');
    }

    public function update(UpdateDictionaryEntryRequest $request, string $dictionary, int $entry, ReferenceDictionaryService $dictionaries): RedirectResponse
    {
        $this->authorize('directories.manage');

        $definition = DictionaryRegistry::definition($dictionary);
        $modelClass = $definition['model'];

        /** @var class-string<AbstractDictionary> $modelClass */
        $model = $modelClass::query()->findOrFail($entry);

        $dictionaries->update(
            entry: $model,
            modelClass: $definition['model'],
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Элемент справочника обновлен.');
    }

    public function destroy(Request $request, string $dictionary, int $entry, ReferenceDictionaryService $dictionaries): RedirectResponse
    {
        $this->authorize('directories.manage');

        $definition = DictionaryRegistry::definition($dictionary);
        $modelClass = $definition['model'];

        /** @var class-string<AbstractDictionary> $modelClass */
        $model = $modelClass::query()->findOrFail($entry);

        $dictionaries->deactivate(
            entry: $model,
            modelClass: $definition['model'],
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Элемент справочника деактивирован.');
    }
}
