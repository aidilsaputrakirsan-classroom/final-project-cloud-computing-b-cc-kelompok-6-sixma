<aside id="admin-sidebar"
       class="w-64 bg-[#020617] border-r border-[#1e293b] h-screen fixed top-0 left-0 flex flex-col text-gray-300 transition-all duration-300">

    {{-- LOGO / BRAND --}}
    <div class="p-6 border-b border-[#1e293b] flex items-center gap-3">
        <div class="w-9 h-9 rounded-2xl bg-indigo-500/10 flex items-center justify-center">
            <img src="/images/logo-sm.png" class="w-6 h-6" alt="Logo">
        </div>
        <span class="sidebar-label font-semibold text-base text-white tracking-tight">
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

            <a href="{{ route('admin.dashboard') }}"
               title="Dashboard"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm
                      {{ request()->routeIs('admin.dashboard')
                            ? 'bg-[#111827] text-white shadow-sm'
                            : 'text-gray-400 hover:bg-[#111827]/60 hover:text-white' }}
                      transition-colors">
                <iconify-icon icon="solar:widget-5-bold-duotone" class="text-xl"></iconify-icon>
                <span class="sidebar-label">
                    Dashboard
                </span>
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
                      text-gray-400 hover:bg-[#111827]/60 hover:text-white
                      transition-colors">
                <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="text-xl"></iconify-icon>
                <span class="sidebar-label">
                    Users
                </span>
            </a>
        </div>

    </nav>

</aside>
