<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- reCAPTCHA -->
        <div class="mt-4">
            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            <x-input-error :messages="$errors->get('g-recaptcha-response')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    @if(config('recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}"></script>
        <script>
            (function() {
                const form = document.querySelector('form');
                const recaptchaInput = document.getElementById('g-recaptcha-response');
                const siteKey = '{{ config('recaptcha.site_key') }}';
                let isSubmitting = false;

                function waitForGrecaptcha() {
                    return new Promise(function(resolve) {
                        if (typeof grecaptcha !== 'undefined') {
                            resolve();
                        } else {
                            const checkInterval = setInterval(function() {
                                if (typeof grecaptcha !== 'undefined') {
                                    clearInterval(checkInterval);
                                    resolve();
                                }
                            }, 100);

                            setTimeout(function() {
                                clearInterval(checkInterval);
                                resolve();
                            }, 5000);
                        }
                    });
                }

                function executeRecaptcha() {
                    return new Promise(function(resolve) {
                        if (typeof grecaptcha === 'undefined') {
                            resolve(null);
                            return;
                        }

                        try {
                            grecaptcha.execute(siteKey, {action: 'register'}).then(function(token) {
                                if (recaptchaInput) {
                                    recaptchaInput.value = token;
                                }
                                resolve(token);
                            }).catch(function() {
                                resolve(null);
                            });
                        } catch (err) {
                            resolve(null);
                        }
                    });
                }

                waitForGrecaptcha().then(function() {
                    executeRecaptcha();
                });

                setInterval(function() {
                    if (!isSubmitting && typeof grecaptcha !== 'undefined') {
                        executeRecaptcha();
                    }
                }, 120000);

                if (form) {
                    form.addEventListener('submit', function(e) {
                        if (isSubmitting) {
                            e.preventDefault();
                            return;
                        }

                        e.preventDefault();
                        isSubmitting = true;

                        executeRecaptcha().then(function() {
                            form.submit();
                        }).catch(function() {
                            isSubmitting = false;
                        });
                    });
                }
            })();
        </script>
    @endif
</x-guest-layout>
