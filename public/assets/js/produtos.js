document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | MODAL
    |--------------------------------------------------------------------------
    */

    const modalButtons = document.querySelectorAll('[data-modal-target]');
    const closeButtons = document.querySelectorAll('[data-close-modal]');

    modalButtons.forEach(button => {

        button.addEventListener('click', () => {

            const modalId = button.dataset.modalTarget;
            const modal = document.getElementById(modalId);

            if (modal) {
                modal.hidden = false;
                document.body.style.overflow = 'hidden';
            }

        });

    });

    closeButtons.forEach(button => {

        button.addEventListener('click', () => {

            const modal = button.closest('.modal-hermex');

            if (modal) {
                modal.hidden = true;
                document.body.style.overflow = '';
            }

        });

    });

    /*
    |--------------------------------------------------------------------------
    | FECHAR MODAL AO CLICAR FORA
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.modal-hermex').forEach(modal => {

        modal.addEventListener('click', (event) => {

            if (event.target === modal) {

                modal.hidden = true;
                document.body.style.overflow = '';

            }

        });

    });

    /*
    |--------------------------------------------------------------------------
    | FECHAR COM ESC
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', (event) => {

        if (event.key === 'Escape') {

            document.querySelectorAll('.modal-hermex').forEach(modal => {

                modal.hidden = true;

            });

            document.body.style.overflow = '';

        }

    });

    /*
    |--------------------------------------------------------------------------
    | PREVIEW DA IMAGEM
    |--------------------------------------------------------------------------
    */

    const inputImagem = document.querySelector('input[name="imagem"]');

    if (inputImagem) {

        inputImagem.addEventListener('change', (event) => {

            const file = event.target.files[0];

            if (!file) return;

            const reader = new FileReader();

            reader.onload = (e) => {

                let preview = document.querySelector('.preview-imagem');

                if (!preview) {

                    preview = document.createElement('img');
                    preview.classList.add('preview-imagem');

                    inputImagem.parentElement.appendChild(preview);

                }

                preview.src = e.target.result;

            };

            reader.readAsDataURL(file);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | BUSCA AUTOMÁTICA
    |--------------------------------------------------------------------------
    */

    const buscaInput = document.querySelector('input[name="busca"]');

    if (buscaInput) {

        let timeout = null;

        buscaInput.addEventListener('keyup', () => {

            clearTimeout(timeout);

            timeout = setTimeout(() => {

                buscaInput.closest('form').submit();

            }, 600);

        });

    }

});