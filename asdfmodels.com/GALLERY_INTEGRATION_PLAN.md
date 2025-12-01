# Gallery Integration Plan - SortableJS + Dropzone.js

## Implementation Steps

1. ✅ Install libraries (npm install sortablejs dropzone)
2. ✅ Update app.js to import libraries
3. ⏳ Add Dropzone CSS
4. ⏳ Replace upload area with Dropzone.js
5. ⏳ Add SortableJS to images grid
6. ⏳ Test and refine

## Key Changes Needed

### 1. Dropzone CSS
Add to layout or view:
```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@6/dist/dropzone.css">
```

### 2. Upload Area (when no images)
Replace custom dropzone with Dropzone.js instance

### 3. Images Grid (when images exist)
Add SortableJS to enable drag-and-drop reordering

### 4. Keep Justified Grid
Maintain the justified grid layout functionality

## Files to Modify

1. `resources/js/app.js` - ✅ Already updated
2. `resources/views/photographers/portfolio/galleries/show.blade.php` - ⏳ In progress
3. `resources/views/layouts/app.blade.php` - May need Dropzone CSS

## Testing Checklist

- [ ] File uploads work with Dropzone
- [ ] Image preview shows correctly
- [ ] Justified grid layout works
- [ ] Drag-to-reorder works with SortableJS
- [ ] No conflicts between Dropzone and SortableJS
- [ ] Mobile/touch support works
- [ ] Error handling works
- [ ] Progress indicators work

