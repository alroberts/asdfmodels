@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 text-gray-900 placeholder-gray-400']) }}>
