@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-gray-300 rounded-md bg-white px-3 py-2 text-gray-900 shadow-sm transition-all duration-200 placeholder-gray-400 focus:border-gray-500 focus:ring-2 focus:ring-gray-200 focus:ring-opacity-60']) }}>
