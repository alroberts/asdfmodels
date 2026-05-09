@include('galleries.form', [
    'role' => 'model',
    'mode' => 'edit',
    'album' => $album,
    'images' => $images,
    'pageTitle' => __('Edit Gallery'),
    'backRoute' => route('portfolio.galleries.show', $album->id),
    'backLabel' => 'Back to Gallery',
    'formAction' => route('portfolio.galleries.update', $album->id),
    'introText' => 'Update your gallery details and settings.',
    'settingsIntro' => 'Configure visibility, content settings, and publication status.',
    'submitLabel' => 'Save Changes',
    'submitIcon' => 'fa-save',
])
