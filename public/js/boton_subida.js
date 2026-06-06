const inputSubida = document.getElementById('entrada_archivo');
const boton = document.getElementById('boton_dinamico');
const icono = document.getElementById('icono_boton');
const texto = document.getElementById('texto_boton');


boton.addEventListener('click', function () {
    if (boton.type === 'button') {
        inputSubida.click();
    }
});

inputSubida.addEventListener('change', function() {
    if (inputSubida.files.length > 0) {

        icono.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                 viewBox="0 0 640 640" fill="#333">
                <path d="M560.3 110.5L420.5 110.5C372.4 110.5 330.6 143.8 320.1 190.8C309.5 143.8 267.8 110.5 219.7 110.5L80 110.5C53.5 110.5 32 132 32 158.5L32 404.3C32 430.8 53.5 452.3 80 452.3L169.7 452.3C271.9 452.3 302.4 476.7 317 527.3C317.7 530.1 322.2 530.1 323 527.3C337.7 476.7 368.2 452.3 470.3 452.3L560 452.3C586.5 452.3 608 430.8 608 404.3L608 158.6C608 132.2 586.7 110.7 560.3 110.5z"/>
            </svg>
        `;

        texto.textContent = "Mostrar";
        boton.title = "Mostrar archivo";
        boton.type = "submit";
    }
});
