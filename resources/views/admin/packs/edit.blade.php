<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editer le pack : {{ $pack->name }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.packs.update', $pack) }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
                @csrf @method('PUT')
                @include('admin.packs._form')
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.packs.index') }}" class="px-4 py-2 text-gray-600 text-sm">Annuler</a>
                    <button class="px-5 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
