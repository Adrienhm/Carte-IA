<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CardType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CardTypeController extends Controller
{
    public function index(): View
    {
        return view('admin.card-types.index', [
            'types' => CardType::withCount('cards')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.card-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        CardType::create($data);

        return redirect()->route('admin.card-types.index')->with('success', 'Type cree.');
    }

    public function edit(CardType $cardType): View
    {
        return view('admin.card-types.edit', ['type' => $cardType]);
    }

    public function update(Request $request, CardType $cardType): RedirectResponse
    {
        $cardType->update($this->validated($request, $cardType));

        return redirect()->route('admin.card-types.index')->with('success', 'Type mis a jour.');
    }

    public function destroy(CardType $cardType): RedirectResponse
    {
        if ($cardType->cards()->exists()) {
            return back()->with('error', 'Impossible : des cartes utilisent ce type.');
        }

        $cardType->delete();

        return redirect()->route('admin.card-types.index')->with('success', 'Type supprime.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?CardType $type = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'prompt_hint' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['slug'] = $this->uniqueSlug($data['name'], $type);

        return $data;
    }

    private function uniqueSlug(string $name, ?CardType $ignore): string
    {
        $base = Str::slug($name) ?: 'type';
        $slug = $base;
        $i = 2;

        while (CardType::where('slug', $slug)->when($ignore, fn ($q) => $q->whereKeyNot($ignore->id))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
