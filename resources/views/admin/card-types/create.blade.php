<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouveau type de carte</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.card-types.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
                @csrf
                @include('admin.card-types._form')
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.card-types.index') }}" class="px-4 py-2 text-gray-600 text-sm">Annuler</a>
                    <button class="px-5 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">Creer</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
