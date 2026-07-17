<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardType;
use App\Models\Rarity;
use App\Services\CardGeneration\CardComposer;
use App\Services\CardGeneration\CardGenerationException;
use App\Services\CardGeneration\CardGenerationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CardController extends Controller
{
    public function index(Request $request): View
    {
        $cards = Card::with('rarity', 'cardType')
            ->when($request->string('rarity')->toString(), fn ($q, $r) => $q->where('rarity_id', $r))
            ->when($request->string('type')->toString(), fn ($q, $t) => $q->where('card_type_id', $t))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.cards.index', [
            'cards' => $cards,
            'rarities' => Rarity::orderBy('sort_order')->get(),
            'types' => CardType::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.cards.create', $this->formData());
    }

    /**
     * Generation IA d'une carte (CDC 5.1) : produit et enregistre directement
     * une carte complete (nom, description, stats, image) a partir du type et
     * de la rarete choisis. L'admin peut ensuite l'editer.
     */
    public function generate(Request $request, CardComposer $composer): RedirectResponse
    {
        $validated = $request->validate([
            'card_type_id' => ['required', 'exists:card_types,id'],
            'rarity_id' => ['required', 'exists:rarities,id'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $cardType = CardType::findOrFail($validated['card_type_id']);
        $rarity = Rarity::findOrFail($validated['rarity_id']);

        try {
            $card = $composer->compose(new CardGenerationRequest($cardType, $rarity, $validated['name'] ?? null));
            $card->save();
        } catch (CardGenerationException $e) {
            // Echec propre + possibilite de reessayer (CDC 9.3).
            return back()->with('error', 'Generation IA echouee : '.$e->getMessage())->withInput();
        }

        return redirect()
            ->route('admin.cards.edit', $card)
            ->with('success', 'Carte generee par IA. Vous pouvez ajuster ses valeurs.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCard($request);
        $data['is_ai_generated'] = false;
        $data['image_path'] = $this->storeUploadedImage($request);

        Card::create($data);

        return redirect()->route('admin.cards.index')->with('success', 'Carte creee.');
    }

    public function edit(Card $card): View
    {
        return view('admin.cards.edit', array_merge($this->formData(), ['card' => $card]));
    }

    public function update(Request $request, Card $card): RedirectResponse
    {
        $data = $this->validateCard($request);

        if ($request->hasFile('image')) {
            $this->deleteImage($card);
            $data['image_path'] = $this->storeUploadedImage($request);
        }

        $card->update($data);

        return redirect()->route('admin.cards.index')->with('success', 'Carte mise a jour.');
    }

    public function destroy(Card $card): RedirectResponse
    {
        // restrictOnDelete au niveau base : une carte encore possedee ou
        // engagee ne peut pas etre supprimee. On le signale proprement.
        if ($card->copies()->exists()) {
            return back()->with('error', 'Impossible : des joueurs possedent encore cette carte. Desactivez-la plutot.');
        }

        $this->deleteImage($card);
        $card->packs()->detach();
        $card->delete();

        return redirect()->route('admin.cards.index')->with('success', 'Carte supprimee.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCard(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'card_type_id' => ['required', 'exists:card_types,id'],
            'rarity_id' => ['required', 'exists:rarities,id'],
            'value' => ['required', 'integer', 'min:0'],
            'power' => ['required', 'integer', 'min:0', 'max:65535'],
            'defense' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function storeUploadedImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store(config('cards.image_dir'), config('cards.image_disk'));
    }

    private function deleteImage(Card $card): void
    {
        if ($card->image_path) {
            Storage::disk(config('cards.image_disk'))->delete($card->image_path);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'types' => CardType::orderBy('name')->get(),
            'rarities' => Rarity::orderBy('sort_order')->get(),
        ];
    }
}
