document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.tabs').forEach(btn => {
        btn.addEventListener('click', () => {

            const tab = btn.dataset.tab;

            // Cambiar el tab en Alpine
            const root = document.querySelector('[x-data]');
            if (root && root.__x) {
                root.__x.$data.tab = tab;
            }

            // Limpiar errores
            document.querySelectorAll('[data-error]').forEach(el => {
                const ul = el.querySelector('ul');
                if (ul) ul.remove();
            });

        });
    });

});
