<div
    x-data="{ show: false, message: '', type: 'success' }"
    x-on:notify.window="
        message = $event.detail.message;
        type = $event.detail.type || 'success';
        show = true;
        setTimeout(() => { show = false }, 3000);
    "
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed bottom-6 right-6 z-50 flex w-full max-w-sm flex-col gap-2 pointer-events-none"
    style="display: none;"
>
    <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-black/5 border-l-4"
         :class="type === 'success' ? 'border-brand-cyan' : 'border-brand-pink'">
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <template x-if="type === 'success'">
                        <x-heroicon-o-check-circle class="h-6 w-6 text-brand-cyan" />
                    </template>
                    <template x-if="type === 'error'">
                        <x-heroicon-o-exclamation-circle class="h-6 w-6 text-brand-pink" />
                    </template>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-gray-900 font-secondary" x-text="message"></p>
                </div>
                <div class="ml-4 flex flex-shrink-0">
                    <button @click="show = false" type="button" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none transition-colors">
                        <span class="sr-only">Cerrar</span>
                        <x-heroicon-m-x-mark class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>