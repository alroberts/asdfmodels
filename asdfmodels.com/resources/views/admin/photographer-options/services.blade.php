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
            <div id="service-toast" class="hidden fixed top-6 right-6 z-50 rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg"></div>
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded border-2 border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div id="service-create" class="bg-white shadow sm:rounded-lg p-6 border-2 border-black mb-6">
                <h3 class="text-lg font-semibold text-black mb-4">Add New Service</h3>
                <form method="POST" action="{{ route('admin.photographer-options.services.store') }}" id="createServiceForm">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="label" :value="__('Label (Display Name)')" />
                            <x-text-input id="label" name="label" type="text" class="block mt-1 w-full" value="{{ old('label') }}" placeholder="e.g., Headshot Sessions" required />
                            <x-input-error :messages="$errors->get('label')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="key" :value="__('Key (URL-friendly)')" />
                            <x-text-input id="key" name="key" type="text" class="block mt-1 w-full" value="{{ old('key') }}" placeholder="e.g., headshot-sessions" required />
                            <p class="mt-1 text-xs text-gray-500">Auto-filled from the label, but you can edit it.</p>
                            <x-input-error :messages="$errors->get('key')" class="mt-2" />
                        </div>
                    </div>
                    <input id="display_order" name="display_order" type="hidden" value="{{ old('display_order', 0) }}">
                    <div class="mt-4">
                        <x-primary-button>
                            <i class="fas fa-plus"></i> Add Service
                        </x-primary-button>
                    </div>
                </form>
            </div>

            <div id="services-list" class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                <h3 class="text-lg font-semibold text-black mb-4">Existing Services</h3>
                <p class="text-sm text-gray-600 mb-4">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Note:</strong> Removing a service will not delete it from user profiles. It will simply be hidden from selection and display. Users who had this service selected will no longer see it, but their data remains intact.
                </p>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Label</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="servicesTableBody" class="bg-white divide-y divide-gray-200">
                            @foreach($services as $service)
                                <tr id="service-{{ $service->id }}" data-id="{{ $service->id }}" data-key="{{ $service->key }}" data-label="{{ $service->label }}" data-order="{{ $service->display_order }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-column="label">{{ $service->label }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900" data-column="key">{{ $service->key }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap" data-column="status">
                                            @if($service->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-column="actions">
                                            <button
                                                type="button"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                data-action="edit-service"
                                                data-id="{{ $service->id }}"
                                                data-key="{{ $service->key }}"
                                                data-label="{{ $service->label }}"
                                                data-display-order="{{ $service->display_order }}"
                                                data-is-active="{{ $service->is_active ? '1' : '0' }}"
                                            >
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('admin.photographer-options.services.delete', $service->id) }}" class="inline service-delete-form">
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
                <p id="servicesEmptyState" class="text-gray-500 {{ $services->count() > 0 ? 'hidden' : '' }}">No services found. Add your first service above.</p>
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
                                    <x-input-label for="edit_label" :value="__('Label')" />
                                    <x-text-input id="edit_label" name="label" type="text" class="block mt-1 w-full" required />
                                    <x-input-error :messages="$errors->get('label')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="edit_key" :value="__('Key')" />
                                    <x-text-input id="edit_key" name="key" type="text" class="block mt-1 w-full" required />
                                    <p class="mt-1 text-xs text-gray-500">Auto-fills from the label until you change it.</p>
                                    <x-input-error :messages="$errors->get('key')" class="mt-2" />
                                </div>
                                <input id="edit_display_order" name="display_order" type="hidden" value="0" />
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
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
        const serviceTableBody = document.getElementById('servicesTableBody');
        const servicesEmptyState = document.getElementById('servicesEmptyState');
        const serviceToast = document.getElementById('service-toast');

        function showServiceToast(message, isError = false) {
            serviceToast.textContent = message;
            serviceToast.className = 'fixed top-6 right-6 z-50 rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg ' + (isError ? 'bg-red-600' : 'bg-gray-900');
            serviceToast.classList.remove('hidden');
            clearTimeout(window.serviceToastTimer);
            window.serviceToastTimer = setTimeout(() => {
                serviceToast.classList.add('hidden');
            }, 2800);
        }

        function serviceStatusHtml(isActive) {
            if (isActive) {
                return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>';
            }

            return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>';
        }

        function serviceActionsHtml(service) {
            return `
                <button
                    type="button"
                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                    data-action="edit-service"
                    data-id="${service.id}"
                    data-key="${service.key}"
                    data-label="${service.label}"
                    data-display-order="${service.display_order ?? 0}"
                    data-is-active="${service.is_active ? '1' : '0'}"
                >
                    <i class="fas fa-edit"></i> Edit
                </button>
                <form method="POST" action="/admin/photographer-options/services/${service.id}" class="inline service-delete-form">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="text-red-600 hover:text-red-900">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            `;
        }

        function upsertServiceRow(service) {
            let row = document.getElementById(`service-${service.id}`);

            if (!row) {
                row = document.createElement('tr');
                row.id = `service-${service.id}`;
                row.className = 'bg-white';
                serviceTableBody.appendChild(row);
            }

            row.dataset.id = service.id;
            row.dataset.key = service.key;
            row.dataset.label = service.label;
            row.dataset.order = service.display_order;
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-column="label">${service.label}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900" data-column="key">${service.key}</td>
                <td class="px-6 py-4 whitespace-nowrap" data-column="status">${serviceStatusHtml(service.is_active)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-column="actions">${serviceActionsHtml(service)}</td>
            `;

            sortServiceRows();
            syncServiceEmptyState();
        }

        function sortServiceRows() {
            const rows = Array.from(serviceTableBody.querySelectorAll('tr'));
            rows.sort((a, b) => {
                return (a.dataset.label || '').localeCompare(b.dataset.label || '', undefined, { sensitivity: 'base' });
            });

            rows.forEach((row) => serviceTableBody.appendChild(row));
        }

        function syncServiceEmptyState() {
            if (!servicesEmptyState) {
                return;
            }

            servicesEmptyState.classList.toggle('hidden', serviceTableBody.children.length > 0);
        }

        function editService(service) {
            document.getElementById('editForm').action = '{{ route("admin.photographer-options.services.update", ":id") }}'.replace(':id', service.id);
            document.getElementById('edit_key').value = service.key;
            document.getElementById('edit_label').value = service.label;
            document.getElementById('edit_display_order').value = service.display_order;
            document.getElementById('edit_is_active').checked = service.is_active;
            editServiceAutoFill.setAutoMode(false);
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function slugifyOptionKey(value) {
            return value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        function bindKeyAutoFill(labelInput, keyInput) {
            let keyTouched = keyInput.value.trim() !== '';

            keyInput.addEventListener('input', () => {
                keyTouched = keyInput.value.trim() !== '';
            });

            labelInput.addEventListener('input', () => {
                if (!keyTouched) {
                    keyInput.value = slugifyOptionKey(labelInput.value);
                }
            });

            return {
                reset() {
                    keyTouched = keyInput.value.trim() !== '';
                },
                setAutoMode(autoMode) {
                    keyTouched = !autoMode;
                },
            };
        }

        const createServiceAutoFill = bindKeyAutoFill(
            document.getElementById('label'),
            document.getElementById('key'),
        );
        const editServiceAutoFill = bindKeyAutoFill(
            document.getElementById('edit_label'),
            document.getElementById('edit_key'),
        );

        async function submitServiceForm(form, onSuccess) {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            const data = await response.json();

            if (!response.ok) {
                const firstError = data.message || Object.values(data.errors || {}).flat()[0] || 'Unable to save service.';
                throw new Error(firstError);
            }

            onSuccess(data);
            showServiceToast(data.status);
        }

        document.getElementById('createServiceForm').addEventListener('submit', async function (event) {
            event.preventDefault();
            const form = event.currentTarget;

            try {
                await submitServiceForm(form, (data) => {
                    upsertServiceRow(data.service);
                    form.reset();
                    createServiceAutoFill.reset();
                });
            } catch (error) {
                showServiceToast(error.message, true);
            }
        });

        document.getElementById('editForm').addEventListener('submit', async function (event) {
            event.preventDefault();

            try {
                await submitServiceForm(event.currentTarget, (data) => {
                    upsertServiceRow(data.service);
                    closeEditModal();
                });
            } catch (error) {
                showServiceToast(error.message, true);
            }
        });

        serviceTableBody?.addEventListener('click', function (event) {
            const editButton = event.target.closest('[data-action="edit-service"]');
            if (!editButton) {
                return;
            }

            editService({
                id: Number(editButton.dataset.id),
                key: editButton.dataset.key,
                label: editButton.dataset.label,
                display_order: Number(editButton.dataset.displayOrder),
                is_active: editButton.dataset.isActive === '1',
            });
        });

        serviceTableBody?.addEventListener('submit', async function (event) {
            const deleteForm = event.target.closest('.service-delete-form');
            if (!deleteForm) {
                return;
            }

            event.preventDefault();

            if (!confirm('Are you sure you want to delete this service? Users who have this service will no longer see it, but their data will remain intact.')) {
                return;
            }

            try {
                const response = await fetch(deleteForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(deleteForm),
                });
                const data = await response.json();

                if (!response.ok) {
                    const firstError = data.message || Object.values(data.errors || {}).flat()[0] || 'Unable to delete service.';
                    throw new Error(firstError);
                }

                document.getElementById(`service-${data.id}`)?.remove();
                syncServiceEmptyState();
                showServiceToast(data.status);
            } catch (error) {
                showServiceToast(error.message, true);
            }
        });

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</x-app-layout>
