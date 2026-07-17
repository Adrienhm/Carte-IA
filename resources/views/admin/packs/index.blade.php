<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Packs</h2>
            <a href="{{ route('admin.packs.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">+ Nouveau pack</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Pack</th>
                            <th class="px-4 py-3">Cartes/ouverture</th>
                            <th class="px-4 py-3">Composition</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($packs as $pack)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $pack->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $pack->cards_per_pack }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $pack->cards_count }} carte(s)</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs {{ $pack->is_active ? 'text-green-600' : 'text-gray-400' }}">{{ $pack->is_active ? 'Actif' : 'Inactif' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.packs.show', $pack) }}" class="text-gray-600 hover:underline">Voir</a>
                                    <a href="{{ route('admin.packs.edit', $pack) }}" class="text-purple-600 hover:underline ms-2">Editer</a>
                                    <form method="POST" action="{{ route('admin.packs.destroy', $pack) }}" class="inline" onsubmit="return confirm('Supprimer ce pack ?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-500 hover:underline ms-2">Suppr.</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
