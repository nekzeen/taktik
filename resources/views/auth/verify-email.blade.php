<x-guest-layout>
    @if (session('status'))
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800 font-medium">
                {{ session('status') }}
            </p>
        </div>
    @else
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Merci de vous être inscrit ! Avant de commencer, veuillez vérifier votre adresse email en cliquant sur le lien que nous venons de vous envoyer. Si vous n'avez pas reçu l'email, nous vous en enverrons un autre avec plaisir.
        </div>
    @endif

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            Un nouveau lien de vérification a été envoyé à l'adresse email que vous avez fournie lors de votre inscription.
        </div>
    @endif

    <div class="mt-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Renvoyer l'email de vérification
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
