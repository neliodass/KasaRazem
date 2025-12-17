(function() {
    const THEME_KEY = 'userTheme';
    
    function getStoredTheme() {
        return localStorage.getItem(THEME_KEY);
    }
    
    function setStoredTheme(theme) {
        localStorage.setItem(THEME_KEY, theme);
    }
    
    function applyTheme(theme) {
        const validTheme = theme === 'dark' ? 'dark' : 'light';
        document.body.classList.remove('theme-light', 'theme-dark');
        document.body.classList.add('theme-' + validTheme);
        return validTheme;
    }
    
    function initTheme() {
        // Sprawdź czy jest zapisany motyw w localStorage
        const storedTheme = getStoredTheme();
        if (storedTheme) {
            // Jeśli jest w localStorage, użyj go
            applyTheme(storedTheme);
        } else {
            // Jeśli nie ma w localStorage, sprawdź czy body już ma klasę motywu
            const hasThemeClass = document.body.classList.contains('theme-light') ||
                                 document.body.classList.contains('theme-dark');

            if (!hasThemeClass) {
                // Jeśli nie ma żadnej klasy motywu, ustaw domyślny
                applyTheme('light');
            }
            // Jeśli body już ma klasę motywu (z serwera), zostaw ją
        }
    }

    // Inicjalizuj motyw gdy DOM jest gotowy
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        // DOM już jest załadowany
        initTheme();
    }
    
    window.ThemeManager = {
        getTheme: function() {
            return document.body.classList.contains('theme-dark') ? 'dark' : 'light';
        },
        
        setTheme: function(theme) {
            const appliedTheme = applyTheme(theme);
            setStoredTheme(appliedTheme);
            return appliedTheme;
        },
        
        toggleTheme: function() {
            const currentTheme = this.getTheme();
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            return this.setTheme(newTheme);
        },
        
        syncWithServer: function(theme) {
            return fetch('/profile/change-theme', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ theme: theme })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    return data.theme;
                } else {
                    throw new Error(data.message || 'Failed to save theme');
                }
            });
        }
    };
})();
