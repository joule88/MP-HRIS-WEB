<div class="bg-white rounded-2xl shadow-[var(--shadow-card)] overflow-hidden border-none ring-1 ring-slate-100 table-container relative">
    <div class="overflow-x-auto min-h-[300px]">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead
                class="bg-slate-50/80 sticky top-0 z-10 backdrop-blur-md text-slate-500 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200">
                <tr>
                    {{ $header }}
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white group/table">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Styling baris tabel yang ada di dalam slot */
    .table-container tbody tr {
        transition: all 0.3s ease;
    }
    .table-container tbody tr:hover {
        background-color: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        z-index: 10;
        position: relative;
    }
    .table-container td {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
</style>
