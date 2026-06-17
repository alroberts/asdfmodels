@include('galleries.form', [
    'role' => 'photographer',
    'mode' => 'edit',
    'gallery' => $gallery,
    'pageTitle' => __('Edit Gallery'),
    'backRoute' => route('portfolio.galleries.show', $gallery->id),
    'backLabel' => 'Back to Gallery',
    'formAction' => route('portfolio.galleries.update', $gallery->id),
    'introText' => 'Update your gallery details and settings.',
    'settingsIntro' => 'Configure visibility, content settings, and publication status.',
    'submitLabel' => 'Save Changes',
    'submitIcon' => 'fa-save',
])
