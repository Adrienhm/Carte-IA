<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-br from-gray-900 via-purple-900 to-indigo-900 text-white antialiased">
    <div class="min-h-full flex flex-col">
        <header class="flex items-center justify-between p-6 max-w-6xl mx-auto w-full">
            <span class="text-lg font-bold">🃏 {{ config('app.name') }}</span>
            <nav class="flex gap-3 text-sm">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-white/10 rounded-lg hover:bg-white/20">Tableau de bord</a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 hover:text-purple-200">Connexion</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-purple-600 rounded-lg hover:bg-purple-700">Inscription</a>
                @endauth
            </nav>
        </header>

        <main class="flex-1 flex items-center">
            <div class="max-w-3xl mx-auto text-center px-6 py-20">
                <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight">
                    Collectionnez, ouvrez, echangez.
                </h1>
                <p class="mt-6 text-lg text-purple-100">
                    Des cartes de collection generees par IA pour l'univers NationsGlory.
                    Ouvrez des packs a raretes ponderees, completez votre collection et
                    echangez avec les autres joueurs.
                </p>
                <div class="mt-10 flex flex-wrap gap-4 justify-center">
                    @guest
                        <a href="{{ route('register') }}" class="px-6 py-3 bg-purple-600 rounded-xl font-semibold hover:bg-purple-700">Commencer a jouer</a>
                        <a href="{{ route('login') }}" class="px-6 py-3 bg-white/10 rounded-xl font-semibold hover:bg-white/20">J'ai deja un compte</a>
                    @endguest
                </div>

                <div class="mt-16 grid grid-cols-1 sm:grid-cols-3 gap-4 text-left">
                    <div class="bg-white/5 rounded-xl p-5">
                        <p class="text-2xl">🎴</p>
                        <p class="font-semibold mt-2">Cartes generees par IA</p>
                        <p class="text-sm text-purple-200 mt-1">Nom, visuel et statistiques uniques.</p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-5">
                        <p class="text-2xl">📦</p>
                        <p class="font-semibold mt-2">Packs a raretes</p>
                        <p class="text-sm text-purple-200 mt-1">Commune, rare, epique, legendaire.</p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-5">
                        <p class="text-2xl">🔁</p>
                        <p class="font-semibold mt-2">Echanges securises</p>
                        <p class="text-sm text-purple-200 mt-1">Transferts atomiques entre joueurs.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
