(function(){
    const body = document.body;
    const THEME_KEY = 'crypyedmanga_theme';

    function applyTheme(theme){
        if(theme === 'light'){
            body.classList.add('light-mode');
        } else {
            body.classList.remove('light-mode');
        }
    }

    function initTheme(){
        const saved = localStorage.getItem(THEME_KEY) || 'dark';
        applyTheme(saved);
        const toggle = document.querySelector('[data-toggle-theme]');
        if(toggle){
            toggle.addEventListener('click', function(){
                const current = body.classList.contains('light-mode') ? 'light' : 'dark';
                const next = current === 'light' ? 'dark' : 'light';
                localStorage.setItem(THEME_KEY, next);
                applyTheme(next);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', initTheme);
})();