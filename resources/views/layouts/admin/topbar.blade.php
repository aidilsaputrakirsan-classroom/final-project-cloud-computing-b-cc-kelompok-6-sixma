<header
    class="w-full bg-[#020617]/95 backdrop-blur border-b border-[#1e293b] px-6 py-4 flex items-center justify-between sticky top-0 z-20">

    <div class="flex items-center gap-3">
        {{-- SIDEBAR TOGGLE --}}
        <button id="sidebar-toggle"
                class="p-2 rounded-lg hover:bg-[#111827] transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/60">
            <iconify-icon icon="solar:hamburger-menu-outline" class="text-xl"></iconify-icon>
        </button>

        <div>
            <h1 class="text-lg font-semibold text-white leading-tight">
                Admin Panel
            </h1>
            <p class="text-xs text-gray-500">
                System Overview
            </p>
        </div>
    </div>

    <div class="flex items-center gap-4">

        {{-- MODE TOGGLE (dummy, bisa kamu isi logic nanti) --}}
        <button id="light-dark-mode"
                class="p-2 rounded-lg hover:bg-[#111827] transition-colors">
            <iconify-icon icon="solar:moon-outline" class="text-xl"></iconify-icon>
        </button>

        {{-- AVATAR --}}
        <button class="rounded-full overflow-hidden w-10 h-10 border border-[#334155] shadow-sm">
            <img src="/images/users/avatar-1.jpg"
                 class="w-full h-full object-cover"
                 alt="Avatar Admin">
        </button>

    </div>

</header>
