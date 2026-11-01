<x-layouts.guest>
    <x-slot:title>Masuk ke Sistem</x-slot:title>

    <div class="bg-white p-6 sm:p-8 rounded-xl border border-zinc-200" x-data="{
        fillCreds(login, pass) {
            $refs.loginInput.value = login;
            $refs.passwordInput.value = pass;
        }
    }">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-xl font-bold tracking-tight text-zinc-900">STAND PKTOS</h1>
            <p class="text-xs text-zinc-500 mt-1">Sistem Pencatatan Penjualan Stand Acara</p>
        </div>

        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg bg-zinc-100 border border-zinc-200 text-zinc-800 text-xs">
                {{ session('status') }}
            </div>
        @endif

        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs">
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
                <label for="login" class="block text-xs font-semibold text-zinc-700 mb-1">Username atau Email</label>
                <input 
                    type="text" 
                    id="login" 
                    name="login" 
                    x-ref="loginInput"
                    value="{{ old('login') }}" 
                    required 
                    autofocus
                    placeholder="misal: admin atau budi"
                    class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 text-xs sm:text-sm"
                >
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-zinc-700 mb-1">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    x-ref="passwordInput"
                    required 
                    placeholder="••••••••"
                    class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 text-xs sm:text-sm"
                >
            </div>

            <div class="flex items-center text-xs">
                <label class="flex items-center gap-2 cursor-pointer select-none text-zinc-600">
                    <input type="checkbox" name="remember" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                    <span>Ingat sesi saya</span>
                </label>
            </div>

            <button 
                type="submit" 
                class="w-full py-2.5 px-4 bg-zinc-900 hover:bg-black text-white font-semibold rounded-lg transition duration-150 text-xs sm:text-sm cursor-pointer"
            >
                Masuk
            </button>
        </form>

        <!-- Quick Credentials for fast multi-device testing -->
        <div class="mt-6 pt-4 border-t border-zinc-100">
            <span class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider block mb-2">Akun Pengujian Cepat:</span>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button 
                    type="button" 
                    @click="fillCreds('admin', 'password123')"
                    class="p-2 text-left rounded border border-zinc-200 hover:bg-zinc-50 transition text-zinc-700"
                >
                    <div class="font-bold text-zinc-900">Admin Stand</div>
                    <div class="text-[11px] text-zinc-500">user: admin</div>
                </button>
                <button 
                    type="button" 
                    @click="fillCreds('budi', 'password123')"
                    class="p-2 text-left rounded border border-zinc-200 hover:bg-zinc-50 transition text-zinc-700"
                >
                    <div class="font-bold text-zinc-900">Kasir (Budi)</div>
                    <div class="text-[11px] text-zinc-500">user: budi</div>
                </button>
            </div>
            <p class="text-[11px] text-zinc-400 text-center mt-2">Password: <code class="text-zinc-600 font-mono">password123</code></p>
        </div>
    </div>
</x-layouts.guest>
