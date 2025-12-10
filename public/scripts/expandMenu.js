document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('dropdown-toggle');
    const menu = document.getElementById('context-menu');
    if (toggleButton && menu) {
        function toggleMenu() {
            const isVisible = menu.classList.toggle('visible');
            toggleButton.setAttribute('aria-expanded', isVisible ? 'true' : 'false');
        }
        toggleButton.addEventListener('click', function(event) {
            event.stopPropagation();
            toggleMenu();
        });
        document.addEventListener('click', function(event) {
            if (menu.classList.contains('visible') && !menu.contains(event.target) && event.target !== toggleButton) {
                menu.classList.remove('visible');
                toggleButton.setAttribute('aria-expanded', 'false');
            }
        });
    }
    const deleteLink = document.querySelector('.dropdown-item.delete-action');
    const modal = document.getElementById('delete-modal');
    const form = document.getElementById('delete-form');
    const cancelButton = document.getElementById('modal-cancel');
    if (deleteLink && modal && form && cancelButton) {
        deleteLink.addEventListener('click', function(event) {
            event.preventDefault();
            const deleteUrl = this.getAttribute('href');
            form.setAttribute('action', deleteUrl);
            if (menu) {
                menu.classList.remove('visible');
                toggleButton.setAttribute('aria-expanded', 'false');
            }
            modal.classList.add('visible');
        });
        cancelButton.addEventListener('click', function() {
            modal.classList.remove('visible');
        });
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.classList.remove('visible');
            }
        });
    }
});