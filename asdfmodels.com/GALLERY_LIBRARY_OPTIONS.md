# Gallery Drag-and-Drop Library Options

This document outlines modern, drop-in solutions for replacing our custom drag-and-drop gallery implementation.

## Top Recommendations

### 1. **SortableJS** ⭐ (Most Recommended)
**Website:** https://sortablejs.github.io/Sortable/  
**GitHub:** https://github.com/SortableJS/Sortable  
**License:** MIT  
**Size:** ~15KB minified

**Pros:**
- ✅ Framework-agnostic (works perfectly with Alpine.js)
- ✅ Very popular (20k+ stars on GitHub)
- ✅ Excellent browser support
- ✅ Touch/mobile support
- ✅ Works with any HTML structure (including justified grids)
- ✅ Lightweight and performant
- ✅ Active maintenance
- ✅ Can be combined with Dropzone.js for file uploads

**Cons:**
- ❌ Doesn't handle file uploads (need separate library)
- ❌ No built-in justified grid layout

**Integration:**
```bash
npm install sortablejs
# or
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
```

**Example with Alpine.js:**
```javascript
Alpine.data('gallery', () => ({
    init() {
        new Sortable(this.$refs.grid, {
            animation: 150,
            ghostClass: 'opacity-50',
            onEnd: (evt) => {
                // Handle reorder
                this.reorderImages(evt.oldIndex, evt.newIndex);
            }
        });
    }
}))
```

**Best for:** Reordering existing images (combine with Dropzone.js for uploads)

---

### 2. **Draggable by Shopify** ⭐⭐
**Website:** https://shopify.github.io/draggable/  
**GitHub:** https://github.com/shopify/draggable  
**License:** MIT  
**Size:** ~30KB minified

**Pros:**
- ✅ Modern, well-architected (by Shopify)
- ✅ Modular (use only what you need)
- ✅ Excellent accessibility support
- ✅ Touch, mouse, and force touch support
- ✅ Framework-agnostic
- ✅ TypeScript support
- ✅ Good documentation

**Cons:**
- ❌ Larger bundle size than SortableJS
- ❌ More complex API
- ❌ Doesn't handle file uploads

**Integration:**
```bash
npm install @shopify/draggable
```

**Best for:** Complex drag-and-drop scenarios requiring fine-grained control

---

### 3. **Dropzone.js + SortableJS** ⭐⭐⭐ (Best Combination)
**Dropzone.js:** https://www.dropzone.dev/  
**SortableJS:** https://sortablejs.github.io/Sortable/

**Pros:**
- ✅ Dropzone.js handles file uploads beautifully
- ✅ SortableJS handles reordering
- ✅ Both are mature, well-maintained libraries
- ✅ Perfect separation of concerns
- ✅ Works great with Alpine.js
- ✅ Dropzone has built-in preview, progress, error handling

**Cons:**
- ❌ Need to integrate two libraries
- ❌ Slightly more setup required

**Integration:**
```bash
npm install dropzone sortablejs
```

**Best for:** Complete solution (uploads + reordering)

---

### 4. **Interact.js**
**Website:** https://interactjs.io/  
**GitHub:** https://github.com/taye/interact.js  
**License:** MIT

**Pros:**
- ✅ Very powerful (drag, resize, gestures)
- ✅ Framework-agnostic
- ✅ Good performance
- ✅ Active development

**Cons:**
- ❌ Overkill for simple drag-and-drop
- ❌ More complex API
- ❌ Doesn't handle file uploads
- ❌ Steeper learning curve

**Best for:** Advanced interactions beyond simple reordering

---

### 5. **Vue Draggable Plus** (If considering Vue migration)
**GitHub:** https://github.com/Alfred-Skyblue/vue-draggable-plus  
**License:** MIT

**Pros:**
- ✅ Built specifically for Vue 3
- ✅ Handles both drag-and-drop and file uploads
- ✅ Modern API

**Cons:**
- ❌ Requires Vue.js (you're using Alpine.js)
- ❌ Would require framework migration

**Best for:** Vue.js projects only

---

## Recommended Approach

### Option A: **SortableJS + Dropzone.js** (Recommended)
**Why:**
- Industry-standard libraries
- Perfect separation: Dropzone for uploads, Sortable for reordering
- Both work seamlessly with Alpine.js
- Mature, battle-tested
- Easy to integrate

**Implementation:**
1. Use Dropzone.js for the file upload dropzone
2. Use SortableJS for reordering uploaded images
3. Keep your justified grid layout code
4. Minimal changes to existing structure

### Option B: **Keep Custom Code** (Current)
**Why:**
- Already working well
- Fully customized to your needs
- No external dependencies
- Complete control

**Consider if:**
- Current implementation meets all requirements
- You want to avoid external dependencies
- Custom behavior is important

---

## Comparison Table

| Library | File Upload | Reordering | Grid Layout | Alpine.js | Bundle Size | Maintenance |
|---------|------------|------------|-------------|-----------|-------------|-------------|
| **SortableJS** | ❌ | ✅ | ✅ | ✅ | ~15KB | ⭐⭐⭐⭐⭐ |
| **Draggable** | ❌ | ✅ | ✅ | ✅ | ~30KB | ⭐⭐⭐⭐ |
| **Interact.js** | ❌ | ✅ | ✅ | ✅ | ~25KB | ⭐⭐⭐⭐ |
| **Dropzone.js** | ✅ | ❌ | ❌ | ✅ | ~20KB | ⭐⭐⭐⭐ |
| **SortableJS + Dropzone** | ✅ | ✅ | ✅ | ✅ | ~35KB | ⭐⭐⭐⭐⭐ |
| **Custom (Current)** | ✅ | ✅ | ✅ | ✅ | 0KB | You |

---

## Next Steps

1. **Test SortableJS + Dropzone.js** in a branch
2. **Compare** performance, UX, and maintainability
3. **Decide** based on:
   - Ease of integration
   - Code maintainability
   - Feature completeness
   - Bundle size impact
   - Long-term support

---

## Resources

- [SortableJS Documentation](https://github.com/SortableJS/Sortable)
- [Dropzone.js Documentation](https://www.dropzone.dev/)
- [Draggable Documentation](https://shopify.github.io/draggable/)
- [Alpine.js Integration Examples](https://alpinejs.dev/)

---

## Recommendation

**Go with SortableJS + Dropzone.js** for:
- ✅ Proven, battle-tested libraries
- ✅ Better long-term maintainability
- ✅ Rich feature sets out of the box
- ✅ Active community support
- ✅ Easy rollback if needed

The integration should be straightforward and will likely result in cleaner, more maintainable code than our custom implementation.

