document.addEventListener('DOMContentLoaded', function() {
    const splitModeToggle = document.querySelector('.split-mode-toggle');
    const splitModeInput = document.getElementById('split_mode');
    const toggleButtons = splitModeToggle.querySelectorAll('button');
    const totalAmountInput = document.getElementById('amount');
    const validationMessage = document.getElementById('amount-validation-message');

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
        const amountInputs = document.querySelectorAll('.split-amount-input');
        const checkboxes = document.querySelectorAll('.split-checkbox');

        ratioInputs.forEach(input => {
            input.style.display = 'none';
            input.disabled = false;
        });
        amountInputs.forEach(input => {
            input.style.display = 'none';
            input.disabled = false;
        });

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
        } else if (mode === 'amount') {
            amountInputs.forEach(input => {
                input.style.display = 'block';
            });

            checkboxes.forEach(checkbox => {
                const userItem = checkbox.closest('.user-split-item');
                const amountInput = userItem.querySelector('.split-amount-input');

                if (!checkbox.checked) {
                    amountInput.disabled = true;
                    amountInput.style.opacity = '0.5';
                    amountInput.value = '0';
                } else {
                    amountInput.disabled = false;
                    amountInput.style.opacity = '1';
                }

                checkbox.removeEventListener('change', handleCheckboxChangeAmount);
                checkbox.addEventListener('change', handleCheckboxChangeAmount);
            });

            amountInputs.forEach(input => {
                input.removeEventListener('input', validateAndAutoFill);
                input.addEventListener('input', validateAndAutoFill);
            });

            // Waliduj przy zmianie całkowitej kwoty
            totalAmountInput.removeEventListener('input', validateAndAutoFill);
            totalAmountInput.addEventListener('input', validateAndAutoFill);
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

    function handleCheckboxChangeAmount(e) {
        const checkbox = e.target;
        const userItem = checkbox.closest('.user-split-item');
        const amountInput = userItem.querySelector('.split-amount-input');

        if (checkbox.checked) {
            amountInput.disabled = false;
            amountInput.style.opacity = '1';
        } else {
            amountInput.disabled = true;
            amountInput.style.opacity = '0.5';
            amountInput.value = '0';
            validateAndAutoFill();
        }
    }

    function validateAndAutoFill() {
        const mode = splitModeInput.value;
        if (mode !== 'amount') return;

        const totalAmount = parseFloat(totalAmountInput.value) || 0;
        const amountInputs = document.querySelectorAll('.split-amount-input:not([disabled])');

        let sum = 0;
        let lastEditableInput = null;
        let emptyInputsCount = 0;

        amountInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            if (value === 0) {
                emptyInputsCount++;
                lastEditableInput = input;
            } else {
                sum += value;
                lastEditableInput = input;
            }
        });

        if (emptyInputsCount === 1 && lastEditableInput && totalAmount > 0) {
            const remaining = totalAmount - sum;
            if (remaining >= 0) {
                lastEditableInput.value = remaining.toFixed(2);
                sum = totalAmount;
            }
        }

        const tolerance = 0.01;
        if (Math.abs(sum - totalAmount) < tolerance) {
            validationMessage.style.display = 'none';
        } else if (totalAmount > 0 && sum > 0) {
            validationMessage.style.display = 'block';
            const diff = totalAmount - sum;
            if (diff > 0) {
                validationMessage.textContent = `Brakuje jeszcze ${diff.toFixed(2)} PLN do pełnej kwoty`;
            } else {
                validationMessage.textContent = `Suma kwot przekracza całkowitą kwotę o ${Math.abs(diff).toFixed(2)} PLN`;
            }
        }
    }

    const currentMode = splitModeInput.value || 'equal';
    updateRatioInputsVisibility(currentMode);
});
