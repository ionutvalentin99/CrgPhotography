const nameCheck = /^[-_a-zA-Z0-9]{4,22}$/;
const tokenCheck = /^[-_/+a-zA-Z0-9]{24,}$/;

// Generate and double-submit a CSRF token in a form field and a cookie, as defined by Symfony's SameOriginCsrfTokenManager
// Use `form.requestSubmit()` to ensure that the submit event is triggered. Using `form.submit()` will not trigger the event
// and thus this event-listener will not be executed.
document.addEventListener('submit', function (event) {
    generateCsrfToken(event.target);
}, true);

export function generateCsrfToken (formElement) {
    const csrfField = formElement.querySelector('input[data-controller="csrf-protection"], input[name="_csrf_token"]');

    if (!csrfField) {
        return;
    }

    let csrfCookie = csrfField.getAttribute('data-csrf-protection-cookie-value');
    let csrfToken = csrfField.value;

    if (!csrfCookie && nameCheck.test(csrfToken)) {
        csrfField.setAttribute('data-csrf-protection-cookie-value', csrfCookie = csrfToken);
        csrfField.defaultValue = csrfToken = btoa(String.fromCharCode.apply(null, (window.crypto || window.msCrypto).getRandomValues(new Uint8Array(18))));
    }
    csrfField.dispatchEvent(new Event('change', { bubbles: true }));

    if (csrfCookie && tokenCheck.test(csrfToken)) {
        const cookie = csrfCookie + '_' + csrfToken + '=' + csrfCookie + '; path=/; samesite=strict';
        document.cookie = window.location.protocol === 'https:' ? '__Host-' + cookie + '; secure' : cookie;
    }
}

/* stimulusFetch: 'lazy' */
export default 'csrf-protection-controller';
