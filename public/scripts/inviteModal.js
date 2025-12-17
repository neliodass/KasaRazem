document.addEventListener('DOMContentLoaded', function() {
    const inviteBtn = document.getElementById('invite-group-btn');
    const inviteModal = document.getElementById('invite-modal');
    const inviteModalClose = document.getElementById('invite-modal-close');
    const copyCodeBtn = document.getElementById('copy-code-btn');
    const copyLinkBtn = document.getElementById('copy-link-btn');
    const inviteCodeInput = document.getElementById('invite-code');
    const inviteLinkInput = document.getElementById('invite-link');

    if (inviteBtn) {
        inviteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (inviteModal) {
                inviteModal.style.display = 'flex';
                setTimeout(() => {
                    inviteModal.classList.add('visible');
                }, 10);
            }
            const dropdownMenu = document.getElementById('context-menu');
            if (dropdownMenu) {
                dropdownMenu.classList.remove('show');
            }
        });
    }

    if (inviteModalClose) {
        inviteModalClose.addEventListener('click', function() {
            closeInviteModal();
        });
    }

    if (inviteModal) {
        inviteModal.addEventListener('click', function(e) {
            if (e.target === inviteModal) {
                closeInviteModal();
            }
        });
    }

    function closeInviteModal() {
        if (inviteModal) {
            inviteModal.classList.remove('visible');
            setTimeout(() => {
                inviteModal.style.display = 'none';
            }, 200);
        }
    }

    if (copyCodeBtn && inviteCodeInput) {
        copyCodeBtn.addEventListener('click', async function() {
            try {
                await navigator.clipboard.writeText(inviteCodeInput.value);
                showCopyFeedback(copyCodeBtn);
            } catch (err) {
                inviteCodeInput.select();
                document.execCommand('copy');
                showCopyFeedback(copyCodeBtn);
            }
        });
    }

    if (copyLinkBtn && inviteLinkInput) {
        copyLinkBtn.addEventListener('click', async function() {
            try {
                await navigator.clipboard.writeText(inviteLinkInput.value);
                showCopyFeedback(copyLinkBtn);
            } catch (err) {
                inviteLinkInput.select();
                document.execCommand('copy');
                showCopyFeedback(copyLinkBtn);
            }
        });
    }

    function showCopyFeedback(button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<span class="material-symbols-outlined">check</span> Skopiowano!';
        button.style.backgroundColor = 'var(--color-success, #10b981)';
        button.style.color = 'white';

        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.backgroundColor = '';
            button.style.color = '';
        }, 2000);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && inviteModal && inviteModal.classList.contains('visible')) {
            closeInviteModal();
        }
    });
});
