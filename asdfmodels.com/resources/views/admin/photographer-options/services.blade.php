<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Photographer Services') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded border-2 border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black mb-6">
                <h3 class="text-lg font-semibold text-black mb-4">Add New Service</h3>
                <form method="POST" action="{{ route('admin.photographer-options.services.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="key" :value="__('Key (URL-friendly)')" />
                            <x-text-input id="key" name="key" type="text" class="block mt-1 w-full" value="{{ old('key') }}" placeholder="e.g., headshot-sessions" required />
                            <p class="mt-1 text-xs text-gray-500">Will be converted to lowercase with hyphens</p>
                            <x-input-error :messages="$errors->get('key')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="label" :value="__('Label (Display Name)')" />
                            <x-text-input id="label" name="label" type="text" class="block mt-1 w-full" value="{{ old('label') }}" placeholder="e.g., Headshot Sessions" required />
                            <x-input-error :messages="$errors->get('label')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="display_order" :value="__('Display Order')" />
                            <x-text-input id="display_order" name="display_order" type="number" class="block mt-1 w-full" value="{{ old('display_order', 0) }}" min="0" />
                            <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                            <x-input-error :messages="$errors->get('display_order')" class="mt-2" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-primary-button>
                            <i class="fas fa-plus"></i> Add Service
                        </x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                <h3 class="text-lg font-semibold text-black mb-4">Existing Services</h3>
                <p class="text-sm text-gray-600 mb-4">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Note:</strong> Removing a service will not delete it from user profiles. It will simply be hidden from selection and display. Users who had this service selected will no longer see it, but their data remains intact.
                </p>
                
                @if($services->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Label</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($services as $service)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">{{ $service->key }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $service->label }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $service->display_order }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($service->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="editService({{ $service->id }}, '{{ $service->key }}', '{{ $service->label }}', {{ $service->display_order }}, {{ $service->is_active ? 'true' : 'false' }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('admin.photographer-options.services.delete', $service->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this service? Users who have this service will no longer see it, but their data will remain intact.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">No services found. Add your first service above.</p>
                @endif
            </div>

            <!-- Edit Modal -->
            <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-semibold text-black mb-4">Edit Service</h3>
                        <form method="POST" id="editForm">
                            @csrf
                            @method('PATCH')
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="edit_key" :value="__('Key')" />
                                    <x-text-input id="edit_key" name="key" type="text" class="block mt-1 w-full" required />
                                    <x-input-error :messages="$errors->get('key')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="edit_label" :value="__('Label')" />
                                    <x-text-input id="edit_label" name="label" type="text" class="block mt-1 w-full" required />
                                    <x-input-error :messages="$errors->get('label')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="edit_display_order" :value="__('Display Order')" />
                                    <x-text-input id="edit_display_order" name="display_order" type="number" class="block mt-1 w-full" min="0" />
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" id="edit_is_active" name="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700">Active</span>
                                    </label>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                    Cancel
                                </button>
                                <x-primary-button>
                                    Update
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editService(id, key, label, displayOrder, isActive) {
            document.getElementById('editForm').action = '{{ route("admin.photographer-options.services.update", ":id") }}'.replace(':id', id);
            document.getElementById('edit_key').value = key;
            document.getElementById('edit_label').value = label;
            document.getElementById('edit_display_order').value = displayOrder;
            document.getElementById('edit_is_active').checked = isActive;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</x-app-layout>

