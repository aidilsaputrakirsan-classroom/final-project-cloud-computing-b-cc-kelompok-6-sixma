<header
    class="w-full bg-white/95 backdrop-blur border-b border-gray-200 px-6 py-4 flex items-center justify-between
           sticky top-0 z-20 dark:bg-[#020617]/95 dark:border-[#1e293b]">

    <!-- LEFT: TITLE / PAGE NAME -->
    <div class="flex items-center gap-3">
        <iconify-icon icon="solar:menu-dots-bold-duotone"
            class="text-2xl cursor-pointer lg:hidden dark:text-gray-300"></iconify-icon>

        <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
            {{ $title ?? 'Dashboard' }}
        </h1>
    </div>

    <!-- RIGHT: USER AVATAR -->
    <div class="flex items-center gap-4">
        <iconify-icon icon="solar:bell-bing-duotone"
            class="text-2xl cursor-pointer text-gray-600 dark:text-gray-300"></iconify-icon>

        <div class="w-10 h-10 rounded-full bg-gray-300 overflow-hidden">
            <img src="/images/avatar.png" alt="User">
        </div>
    </div>

</header>
