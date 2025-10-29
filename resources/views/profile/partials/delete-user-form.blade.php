<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Supprimer le compte
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Une fois votre compte supprimé, toutes ses ressources et données seront définitivement supprimées. Avant de supprimer votre compte, veuillez télécharger les données que vous souhaitez conserver.
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-700 transition"
    >Supprimer le compte</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                Êtes-vous sûr de vouloir supprimer votre compte ?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Une fois votre compte supprimé, toutes ses ressources et données seront définitivement supprimées. Veuillez entrer votre mot de passe pour confirmer la suppression définitive de votre compte.
            </p>

            <div class="mt-6">
                <label for="password" class="sr-only">Mot de passe</label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    placeholder="Mot de passe"
                />

                @error('password', 'userDeletion')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="bg-gray-200 text-gray-900 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">
                    Annuler
                </button>

                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                    Supprimer le compte
                </button>
            </div>
        </form>
    </x-modal>
</section>
