<?php

namespace App\Http\Controllers\Culture;

use App\Enums\SafeguardingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Culture\SaveIntangibleHeritageRequest;
use App\Models\IntangibleHeritage;
use App\Models\Lga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntangibleHeritageController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', IntangibleHeritage::class);

        $filters = $request->only(['search', 'lga_id', 'status']);

        $heritages = IntangibleHeritage::query()
            ->with('lga')
            ->withCount('documents')
            ->filter($filters)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('culture.heritage.index', [
            'heritages' => $heritages,
            'lgas' => Lga::active()->get(),
            'statuses' => SafeguardingStatus::cases(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', IntangibleHeritage::class);

        return view('culture.heritage.create', [
            'lgas' => Lga::active()->get(),
            'statuses' => SafeguardingStatus::cases(),
        ]);
    }

    public function store(SaveIntangibleHeritageRequest $request): RedirectResponse
    {
        $heritage = IntangibleHeritage::create($request->validated() + [
            'created_by' => $request->user()->getKey(),
            'updated_by' => $request->user()->getKey(),
        ]);

        return redirect()
            ->route('culture.heritage.show', $heritage)
            ->with('status', "Intangible heritage record '{$heritage->name}' created.");
    }

    public function show(IntangibleHeritage $intangibleHeritage): View
    {
        $this->authorize('view', $intangibleHeritage);

        $intangibleHeritage->load(['lga', 'creator', 'documents.uploader']);

        return view('culture.heritage.show', [
            'heritage' => $intangibleHeritage,
        ]);
    }

    public function edit(IntangibleHeritage $intangibleHeritage): View
    {
        $this->authorize('update', $intangibleHeritage);

        return view('culture.heritage.edit', [
            'heritage' => $intangibleHeritage,
            'lgas' => Lga::active()->get(),
            'statuses' => SafeguardingStatus::cases(),
        ]);
    }

    public function update(SaveIntangibleHeritageRequest $request, IntangibleHeritage $intangibleHeritage): RedirectResponse
    {
        $intangibleHeritage->update($request->validated() + [
            'updated_by' => $request->user()->getKey(),
        ]);

        return redirect()
            ->route('culture.heritage.show', $intangibleHeritage)
            ->with('status', "Intangible heritage record '{$intangibleHeritage->name}' updated.");
    }

    public function destroy(IntangibleHeritage $intangibleHeritage): RedirectResponse
    {
        $this->authorize('delete', $intangibleHeritage);

        $name = $intangibleHeritage->name;
        $intangibleHeritage->delete();

        return redirect()
            ->route('culture.heritage.index')
            ->with('status', "Intangible heritage record '{$name}' deleted.");
    }
}
