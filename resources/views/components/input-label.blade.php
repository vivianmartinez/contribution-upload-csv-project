@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-[#1F8BA0]']) }}>
    {{ $value ?? $slot }}
</label>
