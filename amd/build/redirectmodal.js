define([], function() {
    /**
     * Mostra um modal customizado reutilizável.
     * @param {string} title - Título do modal.
     * @param {string} message - Mensagem do modal.
     * @param {string|null} redirectUrl - URL para redirecionar após confirmação.
     * @param {boolean} [showCancel=false] - Exibir botão "Cancelar".
     * @param {function|null} [onConfirm=null] - Callback se não quiser redirecionar.
     */
    function showCustomModal(title, message, redirectUrl = null, showCancel = false, onConfirm = null) {
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

        const cleanup = () => {
            backdrop.style.display = 'none';
            confirmBtn.onclick = null;
            cancelBtn.onclick = null;
        };

        confirmBtn.onclick = () => {
            cleanup();
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        };

        cancelBtn.onclick = cleanup;

        // Fechar ao clicar fora do modal
        backdrop.onclick = (e) => {
            if (!modal.contains(e.target)) {
                cleanup();
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                } 
            }
        };
    }

    return {
        show: showCustomModal
    };
});