<div class="modal fade" id="cookieConsentModal" tabindex="-1" aria-labelledby="cookieConsentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title fw-bold" id="cookieConsentModalLabel">Cookie Consent Agreement</h5>
            </div>
            <div class="modal-body">
                <p class="mb-2">In compliance with national data privacy standards and local e-governance policies, Barangay Don Galo Information System (BIS) uses essential cookies and browser storage to keep your session secure, remember your preferences, and improve delivery of online public services.</p>
                <p class="mb-2">By selecting <strong>I Agree</strong>, you confirm that you have read and understood this notice and consent to BIS storing required cookie-related data on this device.</p>
                <p class="mb-0">For full details on data collection, processing, and your rights as a data subject, please review our privacy policy.</p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <a href="/BIS/views/privacy_policy.php" class="btn btn-outline-secondary" target="_blank" rel="noopener noreferrer">View Privacy Policy</a>
                <button type="button" class="btn btn-primary" id="cookieConsentAgreeBtn">I Agree</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    if (window.bisCookieConsentInitialized) {
        return;
    }
    window.bisCookieConsentInitialized = true;

    const consentKey = 'bis_cookie_consent_v1';
    const bootstrapBundleUrl = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';
    let cookieModalInstance = null;

    function hasConsent() {
        return localStorage.getItem(consentKey) === 'accepted';
    }

    function loadBootstrapBundleOnce(callback) {
        if (window.bootstrap && window.bootstrap.Modal) {
            callback();
            return;
        }

        const existingScript = document.getElementById('bis-bootstrap-bundle');
        if (existingScript) {
            existingScript.addEventListener('load', callback, { once: true });
            return;
        }

        const script = document.createElement('script');
        script.id = 'bis-bootstrap-bundle';
        script.src = bootstrapBundleUrl;
        script.defer = true;
        script.addEventListener('load', callback, { once: true });
        document.body.appendChild(script);
    }

    function showCookieModal() {
        const modalElement = document.getElementById('cookieConsentModal');
        if (!modalElement || hasConsent()) {
            return;
        }

        loadBootstrapBundleOnce(function () {
            cookieModalInstance = window.bootstrap.Modal.getOrCreateInstance(modalElement, {
                backdrop: 'static',
                keyboard: false
            });
            cookieModalInstance.show();
        });
    }

    window.bisHasCookieConsent = hasConsent;
    window.bisShowCookieConsentModal = showCookieModal;

    document.addEventListener('DOMContentLoaded', function () {
        const agreeButton = document.getElementById('cookieConsentAgreeBtn');

        if (agreeButton) {
            agreeButton.addEventListener('click', function () {
                localStorage.setItem(consentKey, 'accepted');
                if (cookieModalInstance) {
                    cookieModalInstance.hide();
                }
                document.dispatchEvent(new CustomEvent('bis-cookie-consent-accepted'));
            });
        }

        showCookieModal();
    });
})();
</script>
