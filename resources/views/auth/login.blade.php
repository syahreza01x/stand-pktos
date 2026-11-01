<x-layouts.guest>
    <x-slot:title>Masuk ke Sistem</x-slot:title>

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-xl space-y-6">
        <!-- Header -->
        <div class="text-center sm:text-left">
            <span class="inline-block text-[10px] uppercase font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-md mb-2 tracking-wider">
                Event Sales Stand System
            </span>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">STAND PKTOS</h1>
            <p class="text-xs text-slate-500 mt-1">Sistem Pencatatan & Manajemen Penjualan Stand</p>
        </div>

        @if (session('status'))
            <div class="p-3.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-800 text-xs font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if (session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="font-bold mb-1">Gagal masuk:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Username atau Email</label>
                <input 
                    type="text" 
                    id="login" 
                    name="login" 
                    value="{{ old('login') }}" 
                    required 
                    autofocus
                    placeholder="Masukkan username kasir atau admin"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-medium"
                >
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-medium"
                >
            </div>

            <div class="flex items-center text-xs">
                <label class="flex items-center gap-2 cursor-pointer select-none text-slate-600 font-medium">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span>Ingat sesi saya di perangkat ini</span>
                </label>
            </div>

            <button 
                type="submit" 
                class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold rounded-xl shadow-xs transition duration-150 text-sm cursor-pointer"
            >
                Masuk ke Aplikasi
            </button>
        </form>
    </div>
</x-layouts.guest>
