document.addEventListener('DOMContentLoaded', function () {
    const tabsContainer = document.getElementById('tabs-navigation');
    const tabButtons = tabsContainer ? tabsContainer.querySelectorAll('.tab-button') : [];
    const tabPanels = document.querySelectorAll('.tab-panel');

    function switchTab(targetTab) {
        if (window.innerWidth >= 1024) return;

        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabPanels.forEach(panel => panel.classList.remove('active'));

        const activeBtn = document.querySelector(`.tab-button[data-tab="${targetTab}"]`);
        if (activeBtn) activeBtn.classList.add('active');

        const activePanel = document.querySelector(`.tab-panel[data-tab-content="${targetTab}"]`);
        if (activePanel) {
            activePanel.classList.add('active');
        }

        const mobileAddButton = document.getElementById('mobile-add-button');
        if (mobileAddButton) {
            let buttonText = '+ Dodaj';
            if (targetTab === 'expenses') {
                buttonText = '+ Dodaj Wydatek';
            } else if (targetTab === 'balance') {
                buttonText = '+ Rozlicz Długi';
            } else if (targetTab === 'shopping-lists') {
                buttonText = '+ Dodaj Listę';
            }
            mobileAddButton.textContent = buttonText;
        }
    }

    if (window.innerWidth < 1024) {
        switchTab('expenses');
    } else {
        tabPanels.forEach(panel => panel.classList.remove('active'));
    }

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            const targetTab = this.getAttribute('data-tab');
            switchTab(targetTab);
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth < 1024) {
            const activeTab = document.querySelector('.tab-button.active')?.getAttribute('data-tab') || 'expenses';
            switchTab(activeTab);
        } else {
            tabPanels.forEach(panel => panel.classList.remove('active'));
            const mobileActions = document.getElementById('mobile-bottom-actions');
            if (mobileActions) mobileActions.style.display = 'none';
        }
    });

    if (window.innerWidth < 1024) {
        const mobileActions = document.getElementById('mobile-bottom-actions');
        if (mobileActions) mobileActions.style.display = 'flex';
    }
});