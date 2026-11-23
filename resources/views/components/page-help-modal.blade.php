@php($help = \App\Support\PageHelp::forCurrentRoute())

<div x-data="{ open: false }" @keydown.escape.window="open = false" class="inline-block">
    <button
        type="button"
        @click="open = true"
        class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-1 transition"
        aria-label="Open page guide and form instructions"
        title="Page Guide &amp; Form Help"
    >
        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-emerald-700 text-[10px] font-bold text-white" aria-hidden="true">i</span>
        <span>Page Guide</span>
    </button>

    <!-- Modal Overlay -->
    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="page-help-modal-title"
        @click.self="open = false"
    >
        <div class="w-full max-w-lg rounded-xl bg-white shadow-2xl border border-slate-200 overflow-hidden text-left">
            <div class="flex items-center justify-between bg-emerald-800 px-5 py-3.5 text-white">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/20 text-xs font-bold" aria-hidden="true">i</span>
                    <h3 id="page-help-modal-title" class="text-sm font-bold">{{ $help['title'] }}</h3>
                </div>
                <button
                    type="button"
                    @click="open = false"
                    class="rounded p-1 text-emerald-100 hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-white"
                    aria-label="Close page guide modal"
                >
                    ✕
                </button>
            </div>

            <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto text-sm text-slate-700">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">About This Page</p>
                    <p class="mt-1 text-slate-800 leading-relaxed">{{ $help['summary'] }}</p>
                </div>

                @if(!empty($help['steps']))
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">How to Use &amp; Fill Forms</p>
                    <ol class="mt-2 space-y-2 list-decimal list-inside text-slate-700">
                        @foreach($help['steps'] as $step)
                            <li class="leading-relaxed">{{ $step }}</li>
                        @endforeach
                    </ol>
                </div>
                @endif

                @if(!empty($help['tips']))
                <div class="rounded-md bg-amber-50 border border-amber-200 p-3 text-xs text-amber-900">
                    <span class="font-semibold">Tip:</span> {{ $help['tips'] }}
                </div>
                @endif
            </div>

            <div class="flex justify-end bg-slate-50 border-t border-slate-200 px-5 py-3">
                <button
                    type="button"
                    @click="open = false"
                    class="rounded-md bg-emerald-700 px-4 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600"
                >
                    Got It
                </button>
            </div>
        </div>
    </div>
</div>
