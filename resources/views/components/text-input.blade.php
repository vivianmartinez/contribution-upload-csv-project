@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-[#DBEAFE] focus:ring-r[#DBEAFE] rounded-md shadow-sm']) }}>
