<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Types de cartes</h2>
            <a href="{{ route('admin.card-types.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">+ Nouveau type</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Nom</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Cartes</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($types as $type)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $type->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $type->description }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $type->cards_count }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs {{ $type->is_active ? 'text-green-600' : 'text-gray-400' }}">{{ $type->is_active ? 'Actif' : 'Inactif' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.card-types.edit', $type) }}" class="text-purple-600 hover:underline">Editer</a>
                                    <form method="POST" action="{{ route('admin.card-types.destroy', $type) }}" class="inline" onsubmit="return confirm('Supprimer ce type ?')">
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
