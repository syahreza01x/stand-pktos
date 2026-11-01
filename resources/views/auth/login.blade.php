<x-layouts.guest>
    <x-slot:title>Masuk ke Sistem</x-slot:title>

    <div class="bg-white px-6 py-8 sm:p-10 rounded-2xl shadow-sm border border-slate-200" x-data="{
        fillCreds(login, pass) {
            $refs.loginInput.value = login;
            $refs.passwordInput.value = pass;
        }
    }">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 text-white font-bold text-2xl shadow-sm mb-4">
                🎪
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Event Sales Management</h1>
            <p class="text-sm text-slate-500 mt-1">Sistem Penjualan Stand Organisasi (Stand-PKTOS)</p>
        </div>

        @if (session('status'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if (session('success'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <p class="font-medium mb-1">Gagal masuk:</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="login" class="block text-sm font-medium text-slate-700 mb-1.5">Username atau Email</label>
                <input 
                    type="text" 
                    id="login" 
                    name="login" 
                    x-ref="loginInput"
                    value="{{ old('login') }}" 
                    required 
                    autofocus
                    placeholder="misal: admin atau budi"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition text-sm sm:text-base"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    x-ref="passwordInput"
                    required 
                    placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition text-sm sm:text-base"
                >
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                    <span class="text-slate-600 text-xs sm:text-sm">Ingat sesi saya</span>
                </label>
            </div>

            <button 
                type="submit" 
                class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-base cursor-pointer"
            >
                Masuk Sekarang
            </button>
        </form>

        <!-- Quick Credentials Autofill for fast multi-device testing -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2.5 text-center">Akses Cepat Pengujian (Default Seed)</p>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button 
                    type="button" 
                    @click="fillCreds('admin', 'password123')"
                    class="p-2.5 text-left rounded-lg bg-slate-50 hover:bg-indigo-50 hover:border-indigo-200 border border-slate-200 transition text-slate-700 font-medium"
                >
                    <div class="font-bold text-slate-900">👑 Admin Stand</div>
                    <div class="text-[11px] text-slate-500">user: admin</div>
                </button>
                <button 
                    type="button" 
                    @click="fillCreds('budi', 'password123')"
                    class="p-2.5 text-left rounded-lg bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 border border-slate-200 transition text-slate-700 font-medium"
                >
                    <div class="font-bold text-slate-900">📱 Kasir (Budi)</div>
                    <div class="text-[11px] text-slate-500">user: budi</div>
                </button>
            </div>
            <p class="text-[11px] text-slate-400 text-center mt-2">Password default: <code class="bg-slate-100 px-1 py-0.5 rounded">password123</code></p>
        </div>
    </div>
</x-layouts.guest>
