<aside id="admin-sidebar"
    {{-- KUNCI UTAMA: fixed h-screen (Tinggi penuh) dan w-64 (Lebar) --}}
    class="w-64 bg-white border-r border-gray-200 h-screen fixed top-0 left-0 flex flex-col text-gray-700 transition-all duration-300 dark:bg-[#020617] dark:border-[#1e293b] dark:text-gray-300">

    {{-- LOGO / BRAND --}}
    <div class="p-6 border-b border-gray-200 flex items-center gap-3 dark:border-[#1e293b]">
        <div class="w-9 h-9 rounded-2xl bg-indigo-500/10 flex items-center justify-center">
            <img src="/images/logo-sm.png" class="w-6 h-6" alt="Logo">
        </div>
        <span class="sidebar-label font-semibold text-base text-gray-900 tracking-tight dark:text-white">
            Admin Panel
        </span>
    </div>

    {{-- MENU --}}
    <nav class="flex-1 overflow-y-auto px-3 py-6 space-y-6">

        {{-- MAIN --}}
        <div>
            <p class="sidebar-section-title text-gray-500 uppercase text-[11px] mb-2 tracking-[0.18em]">
                Menu Utama
            </p>

            <a href="{{ route('admin.dashboard') ?? '#' }}"
                title="Dashboard"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm
                        {{ request()->routeIs('admin.dashboard') ?? '' 
                            ? 'bg-indigo-600 text-white shadow-sm'
                            : 'text-gray-600 hover:bg-gray-100/60 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-[#111827]/60 dark:hover:text-white' }}
                        transition-colors">
                <iconify-icon icon="solar:widget-5-bold-duotone" class="text-xl"></iconify-icon>
                <span class="sidebar-label">Dashboard</span>
            </a>
        </div>
        
        {{-- MANAGEMENT --}}
        <div>
            <p class="sidebar-section-title text-gray-500 uppercase text-[11px] mb-2 tracking-[0.18em]">
                Manajemen
            </p>
            <a href="#"
                title="Users"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm
                        text-gray-600 hover:bg-gray-100/60 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-[#111827]/60 dark:hover:text-white
                        transition-colors">
                <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="text-xl"></iconify-icon>
                <span class="sidebar-label">Users</span>
            </a>
        </div>

    </nav>
</aside>