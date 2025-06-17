define([], function() {
    const modalQueue = [];
    let modalActive = false;

    /**
     * Mostra um modal customizado reutilizável.
     * @param {string} title - Título do modal.
     * @param {string} message - Mensagem do modal.
     * @param {string|null} redirectUrl - URL para redirecionar após confirmação.
     * @param {boolean} [showCancel=false] - Exibir botão "Cancelar".
     * @param {function|null} [onConfirm=null] - Callback se não quiser redirecionar.
     */
    function showCustomModal({ title, message, redirectUrl = null, showCancel = false, onConfirm = null, ajaxUrl = null }) {
        const backdrop = document.getElementById('custom-modal-backdrop');
        const modal = document.getElementById('custom-modal');
        const titleElem = document.getElementById('custom-modal-title');
        const messageElem = document.getElementById('custom-modal-message');
        const confirmBtn = document.getElementById('custom-modal-confirm');
        const cancelBtn = document.getElementById('custom-modal-cancel');

        if (!backdrop || !titleElem || !messageElem || !confirmBtn || !cancelBtn) {
            console.error('Modal elements not found in the DOM.');
            return;
        }

        titleElem.textContent = title;
        messageElem.textContent = message;
        cancelBtn.style.display = showCancel ? 'inline-block' : 'none';
        backdrop.style.display = 'flex';
        modalActive = true;

        console.log(ajaxUrl)

        const marcarComoVisto = () => {
            if (ajaxUrl) {
                fetch(ajaxUrl);
            }
        };

        const cleanup = () => {
            backdrop.style.display = 'none';
            confirmBtn.onclick = null;
            cancelBtn.onclick = null;
            backdrop.onclick = null;
            modalActive = false;
            processQueue(); // mostra o próximo modal, se houver
        };

        confirmBtn.onclick = () => {
            marcarComoVisto();
            cleanup();
            if (redirectUrl) {
                window.location.href = redirectUrl;
            } else if (typeof onConfirm === 'function') {
                onConfirm();
            }
        };

        cancelBtn.onclick = () => {
            marcarComoVisto();
            cleanup();
        };

        backdrop.onclick = (e) => {
            if (!modal.contains(e.target)) {
                marcarComoVisto();
                cleanup();
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            }
        };
    }

    function processQueue() {
        if (modalActive || modalQueue.length === 0) {
            return;
        }
        const next = modalQueue.shift();
        showCustomModal(next);
    }

    function show(title, message, redirectUrl = null, showCancel = false, onConfirm = null, ajaxUrl) {
        modalQueue.push({ title, message, redirectUrl, showCancel, onConfirm, ajaxUrl });
        processQueue();
    }

    return {
        show
    };
});