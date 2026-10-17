@php
    /** @var string|null $role */
    $isAdmin = $role === 'admin';
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $isAdmin ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }}">
    {{ $role ?? 'Sin rol' }}
</span>