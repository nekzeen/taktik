<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" id="login-form">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-primary-600 shadow-sm focus:ring-primary-500 dark:focus:ring-primary-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- reCAPTCHA -->
        <div class="mt-4">
            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            <x-input-error :messages="$errors->get('g-recaptcha-response')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    @if(config('recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}"></script>
        <script>
            (function() {
                const form = document.getElementById('login-form');
                const recaptchaInput = document.getElementById('g-recaptcha-response');
                const siteKey = '{{ config('recaptcha.site_key') }}';
                let isSubmitting = false;

                // Wait for grecaptcha to be ready
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
                            // Timeout after 5 seconds
                            setTimeout(function() {
                                clearInterval(checkInterval);
                                resolve();
                            }, 5000);
                        }
                    });
                }

                // Execute reCAPTCHA and update token
                function executeRecaptcha() {
                    return new Promise(function(resolve) {
                        if (typeof grecaptcha === 'undefined') {
                            console.warn('grecaptcha not available');
                            resolve(null);
                            return;
                        }
                        
                        try {
                            grecaptcha.execute(siteKey, {action: 'login'}).then(function(token) {
                                if (recaptchaInput) {
                                    recaptchaInput.value = token;
                                }
                                resolve(token);
                            }).catch(function(err) {
                                console.error('reCAPTCHA error:', err);
                                resolve(null);
                            });
                        } catch (err) {
                            console.error('reCAPTCHA execution error:', err);
                            resolve(null);
                        }
                    });
                }

                // Initialize on page load
                waitForGrecaptcha().then(function() {
                    executeRecaptcha();
                });

                // Refresh reCAPTCHA token periodically (every 2 minutes)
                setInterval(function() {
                    if (!isSubmitting && typeof grecaptcha !== 'undefined') {
                        executeRecaptcha();
                    }
                }, 120000);

                // Handle form submission
                if (form) {
                    form.addEventListener('submit', function(e) {
                        if (isSubmitting) {
                            e.preventDefault();
                            return;
                        }
                        
                        e.preventDefault();
                        isSubmitting = true;
                        
                        // Get fresh reCAPTCHA token before submitting
                        executeRecaptcha().then(function() {
                            // Submit the form normally
                            form.submit();
                        }).catch(function(err) {
                            console.error('Submission error:', err);
                            isSubmitting = false;
                        });
                    });
                }
            })();
        </script>
    @endif
</x-guest-layout>
