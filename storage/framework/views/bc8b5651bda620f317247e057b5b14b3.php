<?php
    $aiActive = ($globalSettings['ai_enabled'] ?? '0') === '1' && !empty($globalSettings['gemini_api_key']);
    $chatActive = ($globalSettings['chatbot_enabled'] ?? '0') === '1';
?>

<?php if($aiActive && $chatActive): ?>
<div x-data="webiconicaChatbot()" x-init="init()" class="fixed bottom-24 right-6 z-50 font-sans">
    <!-- Ventana del Chat -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         style="display: none;"
         class="mb-4 w-[350px] sm:w-[380px] bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col">

        <!-- Cabecera -->
        <div class="bg-brandDark text-white px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-brandLight text-brandDark flex items-center justify-center font-bold shadow-sm">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold leading-tight"><?php echo e($globalSettings['chatbot_name'] ?? 'Asistente Virtual'); ?></h4>
                    <span class="text-[11px] text-emerald-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block animate-pulse"></span> En línea
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button @click="clearChat()" title="Reiniciar conversación" class="text-gray-300 hover:text-white text-xs p-1">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
                <button @click="open = false" class="text-gray-300 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
        </div>

        <!-- Área de Mensajes -->
        <div x-ref="messagesBox" class="p-4 h-80 overflow-y-auto space-y-3 bg-slate-50 text-sm">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user'
                            ? 'bg-brandDark text-white rounded-2xl rounded-br-sm px-3.5 py-2.5 max-w-[82%] shadow-sm'
                            : 'bg-white text-gray-800 border border-gray-200 rounded-2xl rounded-bl-sm px-3.5 py-2.5 max-w-[85%] shadow-sm'">
                        <p class="whitespace-pre-line leading-relaxed" x-text="msg.text"></p>
                    </div>
                </div>
            </template>

            <!-- Indicador escribiendo -->
            <div x-show="loading" style="display: none;" class="flex justify-start">
                <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-sm px-4 py-2.5 shadow-sm flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.15s"></span>
                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
                </div>
            </div>
        </div>

        <!-- Formulario de envío -->
        <form @submit.prevent="send()" class="p-3 bg-white border-t border-gray-200 flex items-center gap-2">
            <input type="text"
                   x-model="input"
                   :disabled="loading"
                   placeholder="Escribe tu consulta aquí..."
                   maxlength="600"
                   class="flex-1 text-sm border border-gray-300 rounded-xl px-3.5 py-2 focus:outline-none focus:border-brandDark">
            <button type="submit"
                    :disabled="loading || !input.trim()"
                    class="bg-brandLight text-brandDark hover:opacity-90 disabled:opacity-40 font-semibold rounded-xl w-10 h-10 flex items-center justify-center transition">
                <i class="fa-solid fa-paper-plane text-sm"></i>
            </button>
        </form>
    </div>

    <!-- Botón Flotante Disparador -->
    <button @click="open = !open; $nextTick(() => scrollToBottom())"
            aria-label="Abrir asistente virtual"
            class="ml-auto w-14 h-14 rounded-full bg-brandDark text-white shadow-xl hover:scale-105 transition flex items-center justify-center relative border-2 border-brandLight">
        <i x-show="!open" class="fa-solid fa-comment-dots text-2xl text-brandLight"></i>
        <i x-show="open" style="display: none;" class="fa-solid fa-chevron-down text-xl"></i>
    </button>
</div>

<script>
function webiconicaChatbot() {
    return {
        open: false,
        loading: false,
        input: '',
        welcomeMessage: <?php echo \Illuminate\Support\Js::from($globalSettings['chatbot_welcome'] ?? '¡Hola! 👋 ¿En qué te puedo ayudar hoy?')->toHtml() ?>,
        messages: [],

        init() {
            const saved = sessionStorage.getItem('webiconica_chat_history');
            if (saved) {
                try {
                    this.messages = JSON.parse(saved);
                } catch (e) {
                    this.messages = [{ role: 'bot', text: this.welcomeMessage }];
                }
            } else {
                this.messages = [{ role: 'bot', text: this.welcomeMessage }];
            }
        },

        saveHistory() {
            sessionStorage.setItem('webiconica_chat_history', JSON.stringify(this.messages.slice(-12)));
        },

        clearChat() {
            this.messages = [{ role: 'bot', text: this.welcomeMessage }];
            sessionStorage.removeItem('webiconica_chat_history');
        },

        scrollToBottom() {
            if (this.$refs.messagesBox) {
                this.$refs.messagesBox.scrollTop = this.$refs.messagesBox.scrollHeight;
            }
        },

        async send() {
            const text = this.input.trim();
            if (!text || this.loading) return;

            this.messages.push({ role: 'user', text });
            this.input = '';
            this.loading = true;
            this.saveHistory();
            this.$nextTick(() => this.scrollToBottom());

            try {
                const res = await fetch('<?php echo e(route('chatbot.message')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: JSON.stringify({
                        message: text,
                        history: this.messages.slice(-8, -1)
                    })
                });

                const data = await res.json();

                if (res.ok && data.status === 'success') {
                    this.messages.push({ role: 'bot', text: data.reply });
                } else {
                    this.messages.push({
                        role: 'bot',
                        text: data.message || 'Ocurrió un error al responder. Intenta de nuevo.'
                    });
                }
            } catch (e) {
                this.messages.push({
                    role: 'bot',
                    text: 'Error de conexión. Verifica tu internet e intenta nuevamente.'
                });
            } finally {
                this.loading = false;
                this.saveHistory();
                this.$nextTick(() => this.scrollToBottom());
            }
        }
    };
}
</script>
<?php endif; ?><?php /**PATH C:\laragon\www\nexus-web\resources\views/partials/chatbot-widget.blade.php ENDPATH**/ ?>