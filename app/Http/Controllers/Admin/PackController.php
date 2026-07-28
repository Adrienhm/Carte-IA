<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Pack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PackController extends Controller
{
    public function index(): View
    {
        return view('admin.packs.index', [
            'packs' => Pack::withCount('cards')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.packs.create', [
            'pack' => new Pack(['cards_per_pack' => 5, 'is_active' => true]),
            'cards' => $this->cardCatalogue(),
            'assigned' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePack($request);
        $data['image_path'] = $this->storeImage($request);

        $pack = Pack::create($data);
        $this->syncCards($pack, $request);

        return redirect()->route('admin.packs.index')->with('success', 'Pack cree.');
    }

    public function show(Pack $pack): View
    {
        $pack->load('cards.rarity');

        return view('admin.packs.show', [
            'pack' => $pack,
            'totalWeight' => $pack->totalWeight(),
        ]);
    }

    public function edit(Pack $pack): View
    {
        $pack->load('cards');

        $assigned = $pack->cards->mapWithKeys(fn (Card $c) => [$c->id => $c->pivot->weight])->all();

        return view('admin.packs.edit', [
            'pack' => $pack,
            'cards' => $this->cardCatalogue(),
            'assigned' => $assigned,
        ]);
    }

    public function update(Request $request, Pack $pack): RedirectResponse
    {
        $data = $this->validatePack($request);

        if ($request->hasFile('image')) {
            if ($pack->image_path) {
                Storage::disk(config('cards.image_disk'))->delete($pack->image_path);
            }
            $data['image_path'] = $this->storeImage($request);
        }

        $pack->update($data);
        $this->syncCards($pack, $request);

        return redirect()->route('admin.packs.index')->with('success', 'Pack mis a jour.');
    }

    public function destroy(Pack $pack): RedirectResponse
    {
        if ($pack->userPacks()->exists() || $pack->openings()->exists()) {
            return back()->with('error', 'Impossible : ce pack est detenu par des joueurs ou a deja ete ouvert. Desactivez-le plutot.');
        }

        $pack->cards()->detach();

        if ($pack->image_path) {
            Storage::disk(config('cards.image_disk'))->delete($pack->image_path);
        }
        $pack->delete();

        return redirect()->route('admin.packs.index')->with('success', 'Pack supprime.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePack(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cards_per_pack' => ['required', 'integer', 'min:1', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
            'weights' => ['array'],
            'weights.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        unset($data['image'], $data['weights']);

        return $data;
    }

    private function syncCards(Pack $pack, Request $request): void
    {
        /** @var array<int, int|string|null> $weights */
        $weights = $request->input('weights', []);

        $sync = [];
        foreach ($weights as $cardId => $weight) {
            $weight = (int) $weight;
            if ($weight > 0) {
                $sync[(int) $cardId] = ['weight' => $weight];
            }
        }

        $pack->cards()->sync($sync);
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('packs', config('cards.image_disk'));
    }

    /**
     * @return \Illuminate\Support\Collection<int, Card>
     */
    private function cardCatalogue()
    {
        return Card::with('rarity', 'cardType')
            ->orderBy('card_type_id')
            ->orderBy('rarity_id')
            ->get();
    }
}
