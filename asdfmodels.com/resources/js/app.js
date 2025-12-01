import './bootstrap';

// Import SortableJS and Dropzone
import Sortable from 'sortablejs';
import Dropzone from 'dropzone';

// Make Sortable available globally for Alpine.js
window.Sortable = Sortable;

// Configure Dropzone (disable auto-discover to use manually)
Dropzone.autoDiscover = false;
window.Dropzone = Dropzone;

// Alpine.js is loaded via CDN in the layout, so we don't import it here
// It will be available as window.Alpine
