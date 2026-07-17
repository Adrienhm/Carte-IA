<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Joueurs</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="GET" class="flex gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Nom ou email..." class="flex-1 rounded-lg border-gray-300 text-sm">
                <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900">Rechercher</button>
            </form>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Joueur</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Cartes</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $user->name }}
                                    @if ($user->isAdmin())<span class="text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded ms-1">admin</span>@endif
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $user->cards_count }}</td>
                                <td class="px-4 py-3">
                                    @if ($user->isBanned())
                                        <span class="text-xs text-red-600">Banni</span>
                                    @else
                                        <span class="text-xs text-green-600">Actif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-purple-600 hover:underline">Gerer</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
