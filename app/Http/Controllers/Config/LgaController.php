<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\SaveLgaRequest;
use App\Models\Lga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LgaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Lga::class);

        $lgas = Lga::query()
            ->when($request->filled('district'), fn ($q) => $q->where('senatorial_district', $request->string('district')->toString()))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search')->toString().'%'))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('config.lgas.index', compact('lgas'));
    }

    public function store(SaveLgaRequest $request): RedirectResponse
    {
        Lga::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('config.lgas.index')->with('status', 'LGA added successfully.');
    }

    public function update(SaveLgaRequest $request, Lga $lga): RedirectResponse
    {
        $lga->update($request->validated() + ['is_active' => $request->boolean('is_active', false)]);

        return redirect()->route('config.lgas.index')->with('status', "LGA {$lga->name} updated.");
    }

    public function destroy(Lga $lga): RedirectResponse
    {
        $this->authorize('delete', $lga);

        $lga->delete();

        return redirect()->route('config.lgas.index')->with('status', "LGA {$lga->name} deleted.");
    }
}
