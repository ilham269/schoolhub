<script setup>
defineProps({ active: { type: String, default: 'kelas' } })

const menu = [
  { key: 'dashboard', label: 'Dashboard', href: '/dashboard/admin', icon: 'M3 10.5 10 4l7 6.5V16a1 1 0 0 1-1 1h-3v-4H7v4H4a1 1 0 0 1-1-1z' },
  { key: 'guru', label: 'Kelola Guru', href: '/dashboard/admin/guru', icon: 'M10 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-6 6a6 6 0 1 1 12 0z' },
  { key: 'siswa', label: 'Kelola Siswa', href: '/dashboard/admin/murid', icon: 'M10 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-6 6a6 6 0 1 1 12 0z' },
  { key: 'kelas', label: 'Kelola Kelas', href: '/dashboard/admin/kelas', icon: 'M3 5h14v10H3zM7 17h6' },
  { key: 'berita', label: 'Kelola Berita', href: '/dashboard/admin/berita', icon: 'M4 4h12v12H4zM6 7h8M6 10h8M6 13h5' },
  { key: 'pengumuman', label: 'Kelola Pengumuman', href: '/dashboard/admin/pengumuman', icon: 'M4 8v4h3l5 3V5L7 8z' },
  { key: 'ppdb', label: 'Pendaftaran PPDB', href: '/dashboard/admin/ppdb', icon: 'M6 3h8v14H6zM8 7h4M8 10h4' },
]
</script>

<template>
  <aside class="flex h-full w-64 shrink-0 flex-col bg-[#0d3b2c] text-emerald-50">
    <div class="flex items-center gap-2.5 px-5 py-6">
      <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-500 text-sm font-bold text-[#0d3b2c]">HB</span>
      <span class="text-lg font-semibold">School <span class="text-emerald-400">Hub</span></span>
    </div>

    <p class="px-5 pb-2 text-[11px] font-semibold tracking-[0.12em] text-emerald-200/50">PORTAL ADMIN</p>

    <nav class="flex-1 space-y-1 px-3">
      <a v-for="item in menu" :key="item.key" :href="item.href"
         class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition"
         :class="active === item.key
           ? 'bg-emerald-600/90 font-medium text-white shadow-sm'
           : 'text-emerald-100/75 hover:bg-white/5 hover:text-white'">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path :d="item.icon" />
        </svg>
        {{ item.label }}
      </a>
    </nav>

    <div class="mt-4 border-t border-white/10 px-5 py-4">
      <div class="flex items-center gap-3">
        <span class="grid h-9 w-9 place-items-center rounded-full bg-emerald-500/90 text-sm font-semibold text-[#0d3b2c]">A</span>
        <div class="leading-tight">
          <p class="text-sm font-medium text-white">Administrator</p>
          <p class="text-xs text-emerald-200/60">Admin</p>
        </div>
      </div>
      <a href="/logout" class="mt-4 flex items-center gap-2 text-sm text-emerald-200/70 transition hover:text-white">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
          <path d="M12 6V4H4v12h8v-2M9 10h8m0 0-2.5-2.5M17 10l-2.5 2.5" />
        </svg>
        Keluar
      </a>
    </div>
  </aside>
</template>
