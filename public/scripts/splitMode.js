document.addEventListener('DOMContentLoaded', function() {
    const splitModeToggle = document.querySelector('.split-mode-toggle');
    const splitModeInput = document.getElementById('split_mode');
    const toggleButtons = splitModeToggle.querySelectorAll('button');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const mode = this.getAttribute('data-mode');

            toggleButtons.forEach(btn => btn.classList.remove('active-mode'));

            this.classList.add('active-mode');

            splitModeInput.value = mode;

            updateRatioInputsVisibility(mode);
        });
    });

    function updateRatioInputsVisibility(mode) {
        const ratioInputs = document.querySelectorAll('.split-ratio-input');
        const checkboxes = document.querySelectorAll('.split-checkbox');

        if (mode === 'ratio') {

            ratioInputs.forEach(input => {
                input.style.display = 'block';
            });

            checkboxes.forEach(checkbox => {
                const userItem = checkbox.closest('.user-split-item');
                const ratioInput = userItem.querySelector('.split-ratio-input');

                if (!checkbox.checked) {
                    ratioInput.disabled = true;
                    ratioInput.style.opacity = '0.5';
                } else {
                    ratioInput.disabled = false;
                    ratioInput.style.opacity = '1';
                }

                checkbox.removeEventListener('change', handleCheckboxChange);
                checkbox.addEventListener('change', handleCheckboxChange);
            });
        } else {

            ratioInputs.forEach(input => {
                input.style.display = 'none';
                input.disabled = false;
            });
        }
    }

    function handleCheckboxChange(e) {
        const checkbox = e.target;
        const userItem = checkbox.closest('.user-split-item');
        const ratioInput = userItem.querySelector('.split-ratio-input');

        if (checkbox.checked) {
            ratioInput.disabled = false;
            ratioInput.style.opacity = '1';
        } else {
            ratioInput.disabled = true;
            ratioInput.style.opacity = '0.5';
        }
    }

    const currentMode = splitModeInput.value || 'equal';
    updateRatioInputsVisibility(currentMode);
});

