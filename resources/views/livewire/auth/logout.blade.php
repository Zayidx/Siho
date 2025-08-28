@if(($variant ?? 'bootstrap') === 'tailwind')
    <button type="button" wire:click="logout" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-md dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600">
        <i class="bi bi-box-arrow-right mr-1"></i> Keluar
    </button>
@else
    <li class="sidebar-item">
        <a href="#" class="sidebar-link text-danger" wire:click.prevent="logout">
            <i class="bi bi-box-arrow-right text-danger"></i>
            <span>Logout</span>
        </a>
    </li>
@endif
