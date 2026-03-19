@extends('layouts.app')

@section('title', 'Tanda Tangan Digital')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Tanda Tangan Digital" subtitle="Buat atau perbarui tanda tangan digital Anda." />

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Tanda Tangan Aktif</h3>
                <div class="bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-center min-h-[200px]">
                    @if($ttd)
                        <img src="{{ asset('storage/' . $ttd->file_ttd) }}" alt="Tanda Tangan" class="max-h-48 object-contain">
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                </path>
                            </svg>
                            <p class="text-sm text-slate-400">Belum ada tanda tangan</p>
                        </div>
                    @endif
                </div>
                @if($ttd)
                    <p class="text-xs text-slate-500 mt-2 text-center">Dibuat: {{ $ttd->created_at?->format('d M Y H:i') }}</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Buat Tanda Tangan Baru</h3>

                <div class="bg-white border-2 border-dashed border-slate-300 rounded-lg relative" style="height: 200px;">
                    <canvas id="signatureCanvas" class="w-full h-full rounded-lg cursor-crosshair"
                        style="touch-action: none;"></canvas>
                </div>

                <div class="flex justify-between mt-3">
                    <button type="button" onclick="clearCanvas()"
                        class="text-sm text-slate-500 hover:text-slate-700 font-medium">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                        Hapus
                    </button>

                    <form action="{{ route('signature.store') }}" method="POST" id="signatureForm">
                        @csrf
                        <input type="hidden" name="signature_data" id="signatureData">
                        <x-button type="submit" id="submitBtn">
                            Simpan Tanda Tangan
                        </x-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        function resizeCanvas() {
            const rect = canvas.parentElement.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            ctx.strokeStyle = '#1e293b';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        canvas.addEventListener('mousedown', (e) => { isDrawing = true; const pos = getPos(e); lastX = pos.x; lastY = pos.y; });
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', () => isDrawing = false);
        canvas.addEventListener('mouseout', () => isDrawing = false);

        canvas.addEventListener('touchstart', (e) => { e.preventDefault(); isDrawing = true; const pos = getPos(e); lastX = pos.x; lastY = pos.y; });
        canvas.addEventListener('touchmove', (e) => { e.preventDefault(); draw(e); });
        canvas.addEventListener('touchend', () => isDrawing = false);

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            lastX = pos.x;
            lastY = pos.y;
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        document.getElementById('signatureForm').addEventListener('submit', function (e) {
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const hasDrawing = imageData.data.some((val, i) => i % 4 === 3 && val > 0);

            if (!hasDrawing) {
                e.preventDefault();
                alert('Silakan gambar tanda tangan terlebih dahulu.');
                return;
            }

            document.getElementById('signatureData').value = canvas.toDataURL('image/png');
        });
    </script>
@endsection
