<script>
function codeEditor() {
    return {
        editor: null, // Aquí guardaremos la instancia de CodeMirror
        
        init() {
            // 1. Recuperar el código fuente de forma segura desde el script JSON
            // Esto evita que el navegador corte el código si hay etiquetas </textarea> dentro.
            let rawContent = '';
            try {
                // Intentamos parsear el contenido JSON inyectado por Blade
                rawContent = JSON.parse(this.$refs.initialData.textContent);
            } catch(e) {
                console.error('Error al desempaquetar el contenido inicial:', e);
                // Si falla, intentamos usar el valor crudo del textarea como plan B
                rawContent = this.$refs.textarea.value;
            }
            
            // 2. Asignar el contenido desempaquetado al textarea real
            this.$refs.textarea.value = rawContent;

            // 3. Inicializar CodeMirror transformando el textarea
            // Asegúrate de que las librerías CDN de CodeMirror estén cargadas en tu layout
            this.editor = CodeMirror.fromTextArea(this.$refs.textarea, {
                lineNumbers: true,    // Mostrar números de línea
                mode: 'htmlmixed',    // Resaltado para HTML (incluye CSS y JS interno)
                theme: 'default',     // Tema visual
                tabSize: 4,           // Tamaño de la tabulación
                indentUnit: 4,        // Unidades de indentación
                lineWrapping: true,   // Ajustar texto largo a la ventana
                viewportMargin: Infinity // Ayuda a que se renderice bien dentro de flexboxes
            });

            // 4. Sincronización ANTES de enviar el formulario
            // CodeMirror no actualiza el textarea automáticamente al teclear por rendimiento.
            // Debemos forzar la actualización (`save()`) cuando se detecte el envío del formulario padre.
            const parentForm = this.$refs.textarea.form;
            if (parentForm) {
                parentForm.addEventListener('submit', () => {
                    // Esto copia el contenido de CodeMirror de vuelta al textarea <textarea name="content">
                    this.editor.save(); 
                });
            }

            // 5. Sincronización opcional (mientras se teclea)
            // Esto mantiene el textarea oculto sincronizado en tiempo real. Útil si usas
            // validación en frontend que dependa del valor del textarea.
            this.editor.on('change', (instance) => {
                this.$refs.textarea.value = instance.getValue();
            });
        },

        // Función para formatear e indentar el código automáticamente
        format() {
            if (!this.editor) return;
            
            // Obtenemos el código desordenado actual
            const unformattedCode = this.editor.getValue();
            
            // Utilizamos la librería js-beautify (html_beautify) cargada por CDN
            const formattedCode = html_beautify(unformattedCode, {
                indent_size: 4,
                indent_char: ' ',
                max_preserve_newlines: 2,
                preserve_newlines: true,
                // Reglas para evitar formatear tags específicos que podrían romperse
                unformatted: ['script', 'style', 'code', 'pre', 'span', 'textarea'],
                wrap_line_length: 0 // No cortar líneas largas automáticamente
            });
            
            // Guardamos la posición actual del cursor para no perderla
            const cursor = this.editor.getCursor();
            
            // Actualizamos el editor con el código ordenado
            this.editor.setValue(formattedCode);
            
            // Restauramos la posición del cursor
            this.editor.setCursor(cursor);
        }
    }
}
</script><?php /**PATH C:\laragon\www\nexus-web\resources\views/admin/posts/partials/editor-js.blade.php ENDPATH**/ ?>