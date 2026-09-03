<div class="relative w-full min-h-full overflow-hidden">

    {{-- ORBES DE LUZ NEÓN DE FONDO --}}
    <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
        <div class="absolute -top-24 left-10 h-96 w-96 rounded-full bg-coral-500/30 blur-[120px] dark:bg-coral-600/25"></div>
        <div class="absolute top-1/3 right-10 h-[30rem] w-[30rem] rounded-full bg-emerald-500/20 blur-[140px] dark:bg-emerald-500/20"></div>
        <div class="absolute -bottom-20 left-1/3 h-96 w-96 rounded-full bg-rose-500/25 blur-[130px] dark:bg-rose-500/20"></div>
    </div>

    {{-- CONTENIDO DE LA VISTA --}}
    <div class="relative z-10">
        {{ $slot }}
    </div>

</div>