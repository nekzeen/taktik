<div id="cookie-consent" style="position: fixed; bottom: 0; left: 0; right: 0; background-color: #111827; color: white; padding: 8px 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); z-index: 9999; border-top: 4px solid #dc2626; display: none;">
    <div style="max-width: 80rem; margin: 0 auto; display: flex; flex-direction: row; align-items: center; justify-content: space-between; gap: 16px;">
        <p style="font-size: 12px; color: #d1d5db; margin: 0;">
            Nous utilisons des cookies essentiels. <a href="{{ route('privacy-policy') }}" style="color: #f87171; text-decoration: underline;">En savoir plus</a>
        </p>
        <div style="display: flex; gap: 8px; white-space: nowrap; flex-shrink: 0;">
            <button id="cookie-reject" style="padding: 4px 16px; background-color: #374151; color: white; border-radius: 6px; font-size: 12px; font-weight: 500; border: none; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#1f2937'" onmouseout="this.style.backgroundColor='#374151'">
                Refuser
            </button>
            <button id="cookie-accept" style="padding: 4px 16px; background-color: #dc2626; color: white; border-radius: 6px; font-size: 12px; font-weight: 500; border: none; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#b91c1c'" onmouseout="this.style.backgroundColor='#dc2626'">
                Accepter
            </button>
        </div>
    </div>
</div>

<script>
    (function() {
        // Check immediately if user has already made a choice
        const hasConsent = localStorage.getItem('cookie-consent');
        const consentBanner = document.getElementById('cookie-consent');
        
        if (hasConsent) {
            if (consentBanner) consentBanner.style.display = 'none';
        } else {
            if (consentBanner) consentBanner.style.display = 'block';
        }

        // Wait for DOM to be ready
        function initCookieConsent() {
            const acceptBtn = document.getElementById('cookie-accept');
            const rejectBtn = document.getElementById('cookie-reject');
            const banner = document.getElementById('cookie-consent');

            if (!acceptBtn || !rejectBtn || !banner) {
                setTimeout(initCookieConsent, 100);
                return;
            }

            acceptBtn.addEventListener('click', function(e) {
                e.preventDefault();
                localStorage.setItem('cookie-consent', 'accepted');
                banner.style.display = 'none';
            });

            rejectBtn.addEventListener('click', function(e) {
                e.preventDefault();
                localStorage.setItem('cookie-consent', 'rejected');
                banner.style.display = 'none';
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCookieConsent);
        } else {
            initCookieConsent();
        }
    })();
</script>
