<button {{ $attributes->merge([
    'class' => 'inline-flex items-center px-4 py-2 bg-[#F7EFA2] text-gray-800 font-semibold rounded-md shadow hover:bg-[#f3e98d] transition'
]) }}>
    {{ $slot }}
</button>
