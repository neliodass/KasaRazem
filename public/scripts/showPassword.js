document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.show-password-btn');

    toggleButtons.forEach(button => {

        const container = button.parentNode;
        const passwordInput = container.querySelector('.password-input');
        if (!passwordInput) return;
        button.addEventListener('mousedown', function() {
            passwordInput.setAttribute('type', 'text');
            this.textContent = 'Ukryj';
        });
        button.addEventListener('mouseup', function() {
            passwordInput.setAttribute('type', 'password');
            this.textContent = 'Pokaż';
        });

        button.addEventListener('mouseleave', function() {
            if (passwordInput.getAttribute('type') === 'text') {
                passwordInput.setAttribute('type', 'password');
                this.textContent = 'Pokaż';
            }
        });
    });
});