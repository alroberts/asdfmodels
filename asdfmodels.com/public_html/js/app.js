// Custom JavaScript for ASDF Models
// Alpine.js is loaded via CDN in the layout files

// CSRF token setup for AJAX requests
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    window.csrfToken = token.getAttribute('content');
}

