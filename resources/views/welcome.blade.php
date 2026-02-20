<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StockMaster API - Documentación y Gestión Avanzada</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Outfit:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/css/welcome.css', 'resources/js/welcome.js'])

    @livewireStyles
</head>

<body class="antialiased bg-zinc-950 text-zinc-400 selection:bg-amber-500/30 selection:text-amber-200">
    <div class="relative min-h-screen">
        <!-- Abstract Background -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-amber-500/10 rounded-full blur-[120px]">
            </div>
            <div class="absolute top-[40%] -right-[10%] w-[30%] h-[50%] bg-blue-500/5 rounded-full blur-[100px]"></div>
        </div>

        <!-- Navigation -->
        <header class="sticky top-0 z-50 glass border-b border-zinc-800/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex items-center gap-3 group">
                        <div
                            class="w-10 h-10 accent-gradient rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-transform duration-300">
                            <span class="text-xl">📦</span>
                        </div>
                        <span
                            class="text-xl sm:text-2xl font-black text-white tracking-tighter uppercase whitespace-nowrap">StockMaster</span>
                    </div>

                    <!-- Desktop Nav -->
                    <div class="hidden lg:flex items-center gap-4">
                        <div class="dropdown">
                            <button
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-zinc-400 hover:text-white transition-colors bg-zinc-900/50 border border-zinc-800 rounded-xl">
                                <span>Ir a Sección</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div class="dropdown-content p-2">
                                <a href="#hero"
                                    class="block px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white rounded-lg transition-colors">🚀
                                    Inicio</a>
                                <a href="#tech-stack"
                                    class="block px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white rounded-lg transition-colors">⚙️
                                    Tecnologías</a>
                                <a href="#architecture"
                                    class="block px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white rounded-lg transition-colors">🏗️
                                    Arquitectura</a>
                                <a href="#patterns"
                                    class="block px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white rounded-lg transition-colors">🧠
                                    Patrones Soft.</a>
                                <a href="#endpoints"
                                    class="block px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white rounded-lg transition-colors">📡
                                    Listado de API</a>
                                <a href="#transfers"
                                    class="block px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white rounded-lg transition-colors">🔄
                                    Traslados</a>
                                <a href="#users"
                                    class="block px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white rounded-lg transition-colors">👥
                                    Usuarios Prueba</a>
                                <a href="#scramble"
                                    class="block px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white rounded-lg transition-colors">✨
                                    Documentación</a>
                            </div>
                        </div>

                        <a href="https://github.com/diegdar/API-StockMaster" target="_blank"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-zinc-800 text-white rounded-xl hover:bg-zinc-700 transition-all duration-300 font-bold text-sm border border-zinc-700 shadow-xl">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                            </svg>
                            <span>GitHub</span>
                        </a>
                        <a href="{{ url('/docs/api') }}" target="_blank"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 text-zinc-950 rounded-xl hover:bg-amber-400 transition-all duration-300 font-black text-sm shadow-lg shadow-amber-500/20">
                            <span>Scramble API</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    </div>

                    <!-- Mobile Burger Button -->
                    <button id="burger-btn"
                        class="lg:hidden p-2 text-zinc-400 hover:text-white bg-zinc-900 border border-zinc-800 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu"
                class="lg:hidden fixed inset-x-4 top-24 glass rounded-3xl z-[60] p-6 shadow-2xl border border-zinc-800">
                <nav class="flex flex-col gap-4">
                    <p class="text-xs font-bold text-amber-500 uppercase tracking-widest mb-2">Saltar a sección</p>
                    <div class="grid grid-cols-2 gap-2 mb-6">
                        <a href="#hero"
                            class="mobile-nav-link p-3 bg-zinc-900/50 rounded-xl text-sm font-bold border border-zinc-800">Inicio</a>
                        <a href="#tech-stack"
                            class="mobile-nav-link p-3 bg-zinc-900/50 rounded-xl text-sm font-bold border border-zinc-800">Tecnología</a>
                        <a href="#architecture"
                            class="mobile-nav-link p-3 bg-zinc-900/50 rounded-xl text-sm font-bold border border-zinc-800">Arquitectura</a>
                        <a href="#endpoints"
                            class="mobile-nav-link p-3 bg-zinc-900/50 rounded-xl text-sm font-bold border border-zinc-800">API
                            endpoints</a>
                    </div>
                    <a href="https://github.com/diegdar/API-StockMaster" target="_blank"
                        class="flex items-center justify-center gap-2 p-4 bg-zinc-800 text-white rounded-xl font-bold">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                        </svg>
                        GitHub Repository
                    </a>
                    <a href="{{ url('/docs/api') }}" target="_blank"
                        class="flex items-center justify-center gap-2 p-4 bg-amber-500 text-zinc-950 rounded-xl font-black">
                        API Documentation
                    </a>
                </nav>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <!-- Hero / Description -->
            <section id="hero" class="mb-32">
                <div class="max-w-4xl">
                    <h2 class="text-zinc-500 font-bold text-xs sm:text-sm tracking-[0.3em] uppercase mb-4">Ingeniería de
                        Software Aplicada</h2>
                    <h1
                        class="text-5xl sm:text-6xl lg:text-7xl font-black text-white tracking-tighter mb-10 leading-[0.9]">
                        Gestión de Stock de Almacenes
                    </h1>
                    <div class="prose prose-invert prose-lg max-w-none text-zinc-400 space-y-6">
                        <p class="leading-relaxed">
                            <strong>StockMaster API</strong> es una solución avanzada de gestión de almacenes diseñada
                            con un enfoque riguroso en la integridad del dato. Desarrollada en <strong>Laravel
                                11</strong>, esta API implementa tipado estricto y una arquitectura modular para
                            garantizar que cada movimiento de stock sea atómico, trazable y seguro.
                        </p>
                        <p class="leading-relaxed">
                            La seguridad se integra desde el núcleo mediante <strong>OAuth2 (Passport)</strong> y un
                            sistema RBAC de granos finos. La automatización mediante observadores de modelos asegura que
                            la auditoría sea silenciosa pero infalible, registrando cada cambio sin afectar el
                            rendimiento de la aplicación.
                        </p>
                        <p class="leading-relaxed">
                            Con su motor de valoración dinámica (FIFO, LIFO, AVG) e integración nativa con
                            <strong>MariaDB InnoDB</strong>, StockMaster está preparado para los requerimientos
                            contables más exigentes.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Livewire Tech Stack & Endpoints -->
            <livewire:welcome-view />

            <!-- Architecture Tree -->
            <section id="architecture" class="mb-32">
                <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6 text-center lg:text-left">
                    <div>
                        <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tighter uppercase mb-2">
                            Estructura Arquitectónica</h2>
                        <p class="text-zinc-500">Organización profesional basada en el desacoplamiento de
                            responsabilidades.</p>
                    </div>
                </div>

                <div
                    class="bg-zinc-900/30 border border-zinc-800 rounded-3xl p-6 sm:p-10 shadow-inner overflow-x-auto scroll-hide">
                    <pre class="code-block text-[12px] sm:text-[15px] leading-relaxed relative">
<span class="text-amber-500 font-bold">📂 API-StockMaster/</span>
├── <span class="text-blue-400">📂 app/</span>
│   ├── <span class="text-emerald-500">📂 DTO/</span>                          <span class="text-zinc-700 italic">// Contenedores inmutables de datos</span>
│   ├── <span class="text-emerald-500">📂 Domain/Inventory/</span>             <span class="text-zinc-700 italic">// Capa de Dominio: Algoritmos de Stock</span>
│   │   ├── <span class="text-zinc-600">📂 Factories/</span>            <span class="text-zinc-700 italic">// Creación dinámica de estrategias</span>
│   │   └── <span class="text-zinc-600">📂 Strategies/</span>           <span class="text-zinc-700 italic">// Implementaciones: FIFO, LIFO, AVG</span>
│   ├── <span class="text-emerald-500">📂 Http/</span>
│   │   ├── <span class="text-zinc-600">📂 Controllers/Api/</span>      <span class="text-zinc-700 italic">// Manejo de peticiones (Thin Controllers)</span>
│   │   └── <span class="text-zinc-600">📂 Resources/</span>            <span class="text-zinc-700 italic">// Transformación a JSON OpenAPI complatible</span>
│   ├── <span class="text-emerald-500">📂 Repositories/</span>                 <span class="text-zinc-700 italic">// Abstracción de acceso a datos</span>
│   └── <span class="text-emerald-500">📂 Services/</span>                     <span class="text-zinc-700 italic">// Orquestación de Lógica de Negocio</span>
├── <span class="text-blue-400">📂 database/</span>                         <span class="text-zinc-700 italic">// Migraciones con InnoDB y Seeders modulares</span>
└── <span class="text-blue-400">📂 tests/</span>                            <span class="text-zinc-700 italic">// Feature y Unit tests (PHPUnit)</span>
                    </pre>
                </div>
            </section>

            <!-- Software Engineering Deep Dive -->
            <section id="patterns" class="mb-32">
                <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tighter uppercase mb-16 text-center">
                    Fundamentos de Ingeniería</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="glass p-8 rounded-3xl border-zinc-800 space-y-6 lg:col-span-2">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="p-2 bg-amber-500/10 rounded-xl text-amber-500">🧠</div>
                            <h3 class="text-xl sm:text-2xl font-black text-white uppercase italic tracking-tighter">
                                Patrones de Diseño de Alto Nivel</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <h4
                                    class="text-amber-500 font-bold tracking-widest text-[11px] uppercase border-b border-amber-500/20 pb-1">
                                    Repository Pattern</h4>
                                <p class="text-[13px] leading-relaxed">Centraliza la selección de datos. Evita que la
                                    lógica de negocio dependa de Eloquent.</p>
                            </div>
                            <div class="space-y-3">
                                <h4
                                    class="text-amber-500 font-bold tracking-widest text-[11px] uppercase border-b border-amber-500/20 pb-1">
                                    Service Pattern</h4>
                                <p class="text-[13px] leading-relaxed">Mantiene los controladores 100% libres de lógica
                                    de negocio.</p>
                            </div>
                            <div class="space-y-3">
                                <h4
                                    class="text-amber-500 font-bold tracking-widest text-[11px] uppercase border-b border-amber-500/20 pb-1">
                                    Strategy Pattern</h4>
                                <p class="text-[13px] leading-relaxed">Inyectamos dinámicamente el algoritmo de
                                    valoración según el producto.</p>
                            </div>
                            <div class="space-y-3">
                                <h4
                                    class="text-amber-500 font-bold tracking-widest text-[11px] uppercase border-b border-amber-500/20 pb-1">
                                    Observer Pattern</h4>
                                <p class="text-[13px] leading-relaxed">Garantiza auditorías silenciosas detectando
                                    eventos de base de datos.</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass p-8 rounded-3xl border-zinc-800 flex flex-col justify-between">
                        <div>
                            <div
                                class="w-12 h-12 bg-blue-500/10 rounded-xl flex items-center justify-center text-blue-500 mb-6 font-black text-xl">
                                DTO</div>
                            <h3
                                class="text-xl sm:text-2xl font-black text-white uppercase mb-4 tracking-tighter leading-none">
                                Desacoplamiento con DTOs</h3>
                            <p class="text-sm leading-relaxed text-zinc-400 mb-4">
                                Prohibimos <code>$request->all()</code>. Transformamos peticiones en objetos inmutables
                                validados.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Case Study: Stock Transfer -->
            <section id="transfers" class="mb-32">
                <div class="glass overflow-hidden rounded-[30px] sm:rounded-[50px] border-zinc-800 shadow-2xl">
                    <div class="p-8 sm:p-20">
                        <h2
                            class="text-3xl text-center sm:text-5xl font-black text-white tracking-tighter uppercase mb-6 leading-none">
                            Traslados: El Corazón Logístico</h2>
                        <!-- Process Diagram -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                            <div class="space-y-8">
                                <div class="flex gap-6 border-b border-zinc-800 pb-8">
                                    <div
                                        class="w-10 h-10 accent-gradient rounded-3xl flex items-center justify-center text-zinc-950 font-black flex-shrink-0">
                                        01</div>
                                    <div>
                                        <h4 class="text-white font-black text-lg uppercase mb-2">Requisitos de Almacén
                                        </h4>
                                        <p class="text-xs text-zinc-500 italic mb-3">Condiciones de infraestructura
                                            antes de operar.</p>
                                        <ul class="text-[11px] space-y-2 text-zinc-400">
                                            <li class="flex items-center gap-2"><span class="text-amber-500">✔</span>
                                                Almacenes Origen/Destino deben existir en sistema.</li>
                                            <li class="flex items-center gap-2"><span class="text-amber-500">✔</span> El
                                                Almacén de <strong class="text-zinc-300">Destino debe estar
                                                    Activo</strong>.</li>
                                            <li class="flex items-center gap-2"><span class="text-amber-500">✔</span>
                                                IDs de origen y destino deben ser estrictamente distintos.</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="flex gap-6 border-b border-zinc-800 pb-8">
                                    <div
                                        class="w-10 h-10 accent-gradient rounded-3xl flex items-center justify-center text-zinc-950 font-black flex-shrink-0">
                                        02</div>
                                    <div>
                                        <h4 class="text-white font-black text-lg uppercase mb-2">Validaciones Reales
                                        </h4>
                                        <p class="text-xs text-zinc-500 italic mb-3">Protección contra inconsistencias
                                            de inventario.</p>
                                        <ul class="text-[11px] space-y-2 text-zinc-400">
                                            <li class="flex items-center gap-2"><span class="text-amber-500">📊</span>
                                                <strong>Suficiencia de Stock</strong>: Verificación en tiempo real del
                                                almacén emisor.
                                            </li>
                                            <li class="flex items-center gap-2"><span class="text-amber-500">🏗️</span>
                                                <strong>Control de Capacidad</strong>: El receptor debe tener espacio
                                                físico disponible.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="flex gap-6">
                                    <div
                                        class="w-10 h-10 bg-red-500/20 rounded-3xl flex items-center justify-center text-red-500 border border-red-500/30 font-black flex-shrink-0">
                                        ⚠️</div>
                                    <div>
                                        <h4 class="font-black text-lg uppercase mb-2 text-red-400">Fallo
                                            Atómico (Rollback)</h4>
                                        <p class="text-xs text-zinc-400 leading-relaxed">
                                            Nuestro sistema sigue el principio de <strong class="text-white">"Todo o
                                                Nada"</strong>. Si durante el proceso de traslado ocurre un error (falta
                                            de stock de último minuto, pérdida de conexión o violación de capacidad), la
                                            <strong>transacción completa se deshace (Rollback)</strong>.
                                        </p>
                                        <p class="text-[10px] text-zinc-500 mt-2 italic">Esto garantiza que nunca
                                            existan "items fantasmales" que salieron de un almacén pero nunca llegaron
                                            al otro.</p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="bg-zinc-950/80 rounded-3xl p-8 border border-zinc-800 overflow-x-auto scroll-hide">
                                <h3 class="text-amber-500 font-bold text-[10px] uppercase tracking-[0.3em] mb-8">Flujo
                                    de Ejecución Técnica</h3>
                                <div class="space-y-4 font-mono text-[11px] whitespace-nowrap lg:whitespace-normal">
                                    <div class="text-emerald-500/80 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        DB::beginTransaction()
                                    </div>
                                    <div class="flex flex-col gap-2 pl-4 border-l border-zinc-800">
                                        <div class="flex items-center gap-3 text-zinc-500"><span>1. Request ->
                                                TransferStockDTO</span></div>
                                        <div class="flex items-center gap-3 text-zinc-500"><span>2.
                                                validateSufficientStock($source)</span></div>
                                        <div class="flex items-center gap-3 text-zinc-500"><span>3.
                                                validateDestinationCapacity($dest)</span></div>
                                        <div class="flex items-center gap-3 text-zinc-400 font-bold"><span>4.
                                                movementRepository->create('OUT')</span></div>
                                        <div class="flex items-center gap-3 text-zinc-400 font-bold"><span>5.
                                                movementRepository->create('IN')</span></div>
                                    </div>
                                    <div class="text-emerald-500/80 pt-2">✅ DB::commit() <span
                                            class="text-zinc-600 ml-2">// Éxito Total</span></div>
                                    <div class="text-red-500/60 pt-1">❌ DB::rollBack() <span
                                            class="text-zinc-600 ml-2">// Si falla paso 2, 3, 4 o 5</span></div>
                                </div>
                                <div class="mt-12 flex flex-col items-center justify-center gap-2 p-2 bg-zinc-900/50 rounded-xl border border-zinc-800">
                                    <p class="text-[10px] text-zinc-500 uppercase font-black mb-2 tracking-widest">
                                        Estado del Almacén</p>
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-500 text-[9px] font-bold">ACTIVE_REQUIRED</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Test Users & Scramble -->
            <section id="users" class="mb-10">
                <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tighter uppercase mb-2 text-center">
                    Entorno Sandbox</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center mt-12">
                    <div class="glass p-10 rounded-3xl border-zinc-800 group">
                        <div class="text-5xl mb-6 grayscale group-hover:grayscale-0 transition-all">👑</div>
                        <h4 class="text-xl font-black text-white uppercase mb-2">Admin</h4>
                        <p class="text-zinc-500 text-[10px] mb-6 font-mono">admin@stockmaster.com</p>
                        <span
                            class="px-4 py-1.5 rounded-full bg-zinc-950 text-amber-500 font-mono text-xs border border-zinc-800">Password$1234</span>
                    </div>
                    <div class="glass p-10 rounded-3xl border-zinc-800 group">
                        <div class="text-5xl mb-6 grayscale group-hover:grayscale-0 transition-all">🏗️</div>
                        <h4 class="text-xl font-black text-white uppercase mb-2">Worker</h4>
                        <p class="text-zinc-500 text-[10px] mb-6 font-mono">worker@stockmaster.com</p>
                        <span
                            class="px-4 py-1.5 rounded-full bg-zinc-950 text-amber-500 font-mono text-xs border border-zinc-800">Password$1234</span>
                    </div>
                    <div class="glass p-10 rounded-3xl border-zinc-800 group">
                        <div class="text-5xl mb-6 grayscale group-hover:grayscale-0 transition-all">👀</div>
                        <h4 class="text-xl font-black text-white uppercase mb-2">Viewer</h4>
                        <p class="text-zinc-500 text-[10px] mb-6 font-mono">viewer@stockmaster.com</p>
                        <span
                            class="px-4 py-1.5 rounded-full bg-zinc-950 text-amber-500 font-mono text-xs border border-zinc-800">Password$1234</span>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-zinc-900 bg-zinc-950 py-10">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <span class="text-4xl font-black text-white tracking-[0.2em] opacity-10 uppercase">StockMaster API @
                    2026</span>
            </div>
            <!-- Copyright -->
            <div class="flex gap-3 justify-center items-center fixed bottom-0 w-full text-center py-1  text-sm">
                {{-- my web-icon --}}
                <div class="">
                    <a href="https://diegochacondev.es" target="_blank">
                        <img src="{{ asset('images/logos/my-web-logo.webp') }}" class="w-[50px] h-[40px]"
                            alt="logo Diego Chacon que redirige a su sitio web" title="Ir a portfolio Diego Chacon" />
                    </a>
                </div>
                <div>
                    &copy; Developed by:
                    <a href="https://diegochacondev.es/" target="_blank" rel="noopener noreferrer"
                        class="underline hover:text-yellow-400">
                        Diego Chacon Delgado
                    </a>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>

</html>