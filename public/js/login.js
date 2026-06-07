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

    const form = document.querySelector('form');
    const btn = document.querySelector('.submit-btn');

    if (form && btn) {
        form.addEventListener('submit', () => {

            const text = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.btn-spinner');

            if (text && spinner) {
                text.classList.add('hidden');
                spinner.classList.remove('hidden');
            }

            btn.disabled = true;
        });
    }

});