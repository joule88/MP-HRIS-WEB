@extends('layouts.app')
@section('title', 'Notifikasi')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Notifikasi</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar semua notifikasi Anda</p>
        </div>
        @if($unreadCount > 0)
            <form id="mark-all-read-form" onsubmit="return false;">
                <x-button type="button" variant="secondary" onclick="markAllReadPage()">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    Tandai Semua Dibaca ({{ $unreadCount }})
                </x-button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        @forelse($notifikasi as $item)
            <div class="flex items-start gap-4 px-5 py-4 border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition-colors cursor-pointer notif-item {{ !$item->is_read ? 'bg-blue-50/30' : '' }}"
                 data-id="{{ $item->id }}"
                 data-read="{{ $item->is_read ? '1' : '0' }}"
                 onclick="handleNotifClick(this)">

                <div class="flex-shrink-0 mt-0.5">
                    @php
                        $iconConfig = match($item->tipe) {
                            'lembur' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />'],
                            'presensi' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />'],
                            'pengumuman' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />'],
                            'surat_izin', 'izin' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />'],
                            'poin' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />'],
                            default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />'],
                        };
                    @endphp
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center {{ $iconConfig['bg'] }} {{ $iconConfig['text'] }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">{!! $iconConfig['icon'] !!}</svg>
                    </span>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-800 {{ !$item->is_read ? 'font-bold' : '' }}">{{ $item->judul }}</p>
                            <p class="text-sm text-slate-500 mt-0.5">{{ $item->pesan }}</p>
                        </div>
                        @if(!$item->is_read)
                            <span class="flex-shrink-0 mt-1.5 w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5">{{ $item->created_at->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <div class="px-5 py-16 text-center">
                <svg class="w-16 h-16 mx-auto text-slate-200 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                <p class="text-slate-400 font-medium">Belum ada notifikasi</p>
                <p class="text-sm text-slate-300 mt-1">Notifikasi akan muncul saat ada aktivitas terkait Anda</p>
            </div>
        @endforelse
    </div>

    @if($notifikasi->hasPages())
        <div class="mt-6">
            {{ $notifikasi->links() }}
        </div>
    @endif
@endsection

@section('script')
<script>
    function handleNotifClick(el) {
        var id = el.getAttribute('data-id');
        var isRead = el.getAttribute('data-read') === '1';

        if (!isRead) {
            fetch('/notifikasi/' + id + '/read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).then(function() {
                el.classList.remove('bg-blue-50/30');
                el.setAttribute('data-read', '1');
                var dot = el.querySelector('.bg-blue-500');
                if (dot) dot.remove();
                var title = el.querySelector('.font-bold');
                if (title) title.classList.remove('font-bold');
            });
        }
    }

    function markAllReadPage() {
        fetch('/notifikasi/read-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(function() {
            window.location.reload();
        });
    }
</script>
@endsection
