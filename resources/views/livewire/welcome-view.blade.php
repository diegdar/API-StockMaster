<div>
    <!-- Images & Tech Stack -->
    <section id="tech-stack" class="mb-32 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <div class="space-y-8 order-2 lg:order-1">
            <div
                class="group relative rounded-3xl overflow-hidden border border-zinc-800 shadow-2xl transition-all duration-500 hover:border-amber-500/50">
                <img src="{{ asset('images/layers-app.png') }}" alt="Arquitectura"
                    class="w-full h-auto grayscale group-hover:grayscale-0 transition-all duration-700">
                <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/80 to-transparent"></div>
                <div class="absolute bottom-6 left-6">
                    <h3 class="text-white font-black uppercase tracking-tighter text-sm sm:text-base">Diagrama de Capas
                        del Sistema</h3>
                </div>
            </div>
            <div
                class="group relative rounded-3xl overflow-hidden border border-zinc-800 shadow-2xl transition-all duration-500 hover:border-blue-500/50">
                <img src="{{ asset('images/screen-app.png') }}" alt="Dashboard"
                    class="w-full h-auto grayscale group-hover:grayscale-0 transition-all duration-700">
                <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/80 to-transparent"></div>
                <div class="absolute bottom-6 left-6">
                    <h3 class="text-white font-black uppercase tracking-tighter text-sm sm:text-base">Vista de Gestión
                        Operativa</h3>
                </div>
            </div>
        </div>

        <div class="order-1 lg:order-2">
            <h3 class="text-white text-3xl sm:text-4xl font-black tracking-tighter mb-8 uppercase">Stack Tecnológico
                <span class="text-zinc-600 block text-lg font-bold">Núcleo de la Aplicación</span></h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($stack as $item)
                    <div
                        class="glass p-6 rounded-2xl border-zinc-800 hover:bg-zinc-900/50 transition-all border-l-2 hover:border-l-amber-500">
                        <h4 class="text-amber-500 font-black text-[10px] uppercase tracking-widest mb-1">{{ $item[0] }}</h4>
                        <p class="text-white font-bold mb-2 text-sm">{{ $item[1] }}</p>
                        <p class="text-[11px] text-zinc-500 leading-relaxed">{{ $item[2] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Comprehensive Endpoint List -->
    <section id="endpoints" class="mb-32">
        <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tighter uppercase mb-4">Referencia Completa de
            API</h2>
        <p class="text-zinc-500 mb-12 max-w-2xl text-balance">Listado organizado de todos los puntos de acceso con sus
            validaciones técnicas y roles requeridos.</p>

        <div class="space-y-16">
            @foreach($apiEntities as $title => $items)
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <h3 class="text-xl font-black text-white uppercase tracking-widest">{{ $title }}</h3>
                        <div class="h-px flex-1 bg-zinc-800"></div>
                    </div>
                    <div class="grid grid-cols-1 gap-2">
                        @foreach($items as $ep)
                            <div
                                class="endpoint-card glass p-4 sm:p-5 rounded-2xl border-zinc-800 flex flex-col md:flex-row md:items-center gap-4 transition-all duration-300 group/ep">
                                <div class="flex items-center gap-3 md:w-48 flex-shrink-0">
                                    <span
                                        class="px-2 py-0.5 rounded-lg text-[10px] font-black {{ str_contains($ep[0], 'POST') ? 'bg-blue-500/10 text-blue-500' : (str_contains($ep[0], 'GET') ? 'bg-green-500/10 text-green-500' : 'bg-amber-500/10 text-amber-500') }}">{{ $ep[0] }}</span>
                                    <span
                                        class="font-mono text-[11px] text-zinc-500 truncate group-hover/ep:text-zinc-300 transition-colors">{{ $ep[1] }}</span>
                                </div>
                                <div class="flex-1">
                                    <h4
                                        class="text-white font-bold text-xs uppercase tracking-tight group-hover/ep:text-amber-500 transition-colors">
                                        {{ $ep[2] }}</h4>
                                    <p class="text-[11px] text-zinc-500 italic mt-0.5 leading-relaxed">{{ $ep[3] }}</p>
                                </div>
                                <div class="md:w-32 flex flex-col items-end gap-1">
                                    <span class="text-[10px] font-bold text-zinc-600 uppercase">{{ $ep[4] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>