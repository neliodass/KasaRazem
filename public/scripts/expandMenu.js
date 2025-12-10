
    document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('dropdown-toggle');
    const menu = document.getElementById('context-menu');

    if (!toggleButton || !menu) return;

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

    menu.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function() {
    menu.classList.remove('visible');
    toggleButton.setAttribute('aria-expanded', 'false');
});
});
});
