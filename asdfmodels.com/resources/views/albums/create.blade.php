@include('galleries.form', [
    'role' => 'model',
    'mode' => 'create',
    'images' => $images,
    'pageTitle' => __('Create New Gallery'),
    'backRoute' => route('portfolio.galleries.index'),
    'backLabel' => 'Back to Portfolio',
    'formAction' => route('portfolio.galleries.store'),
    'introText' => 'Create a new gallery to organise your portfolio images.',
    'settingsIntro' => 'Configure visibility, content settings, and publication status.',
    'submitLabel' => 'Create Gallery',
    'submitIcon' => 'fa-images',
])
