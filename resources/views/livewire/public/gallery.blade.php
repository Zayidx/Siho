<div class="container mx-auto px-4 py-10">
    @include('components.public.breadcrumbs', ['items' => [
        ['label' => 'Beranda', 'url' => url('/')],
        ['label' => 'Galeri']
    ]])

    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2 mb-6">Galeri Hotel</h1>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach($categories as $key => $label)
            <button type="button"
                class="px-3 py-1.5 rounded-md border text-sm {{ $category===$key ? 'border-blue-600 text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200' }}"
                wire:click="$set('category','{{ $key }}')">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @forelse($rows as $img)
            <div class="relative group rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800">
                <img class="w-full h-32 object-cover" src="{{ Storage::disk('public')->exists($img->path) ? Storage::url($img->path) : 'https://placehold.co/400x300' }}" alt="gallery">
                @if($img->category)
                    <span class="absolute top-2 left-2 text-xs px-2 py-0.5 rounded bg-black/60 text-white">{{ ucfirst($img->category) }}</span>
                @endif
            </div>
        @empty
            <div class="col-span-full text-gray-600 dark:text-gray-400">Belum ada foto untuk kategori ini.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $rows->links() }}</div>
</div>
