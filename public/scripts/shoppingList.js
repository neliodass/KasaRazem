document.addEventListener('DOMContentLoaded', () => {
    if (window.currentListId && window.initialItems && window.initialItems.length > 0) {
        renderItems(window.initialItems);
    } else if (window.currentListId) {
        renderItems([]);
    }
});

function loadList(listId, btnElement) {
    window.currentListId = listId;

    document.querySelectorAll('.list-tab-active').forEach(el => {
        el.classList.remove('list-tab-active');
        el.classList.add('list-tab-inactive');
    });
    btnElement.classList.remove('list-tab-inactive');
    btnElement.classList.add('list-tab-active');
    document.querySelectorAll('.list-tab-wrapper.active-list').forEach(el => {
        el.classList.remove('active-list');
    });
    const newWrapper = btnElement.closest('.list-tab-wrapper');
    if (newWrapper) {
        newWrapper.classList.add('active-list');
    }

    fetch(`/groups/${window.groupId}/lists/${listId}/items`)
        .then(res => res.json())
        .then(items => {
            renderItems(items);
        })
        .catch(err => console.error(err));
}

function renderItems(items) {
    const activeContainer = document.getElementById('active-items-list');
    const purchasedContainer = document.getElementById('purchased-items-list');
    const countBadge = document.getElementById('to-buy-count');

    activeContainer.innerHTML = '';
    purchasedContainer.innerHTML = '';

    let activeCount = 0;

    items.forEach(item => {
        const isPurchased = item.isPurchased;
        if (!isPurchased) activeCount++;

        const html = `
            <div class="shopping-item-row group">
                <label class="shopping-item-label">
                    <input type="checkbox"
                           class="shopping-item-checkbox"
                           ${isPurchased ? 'checked' : ''}
                           onchange="toggleItem(${item.id},${isPurchased})">
                    <div class="${isPurchased ? 'purchased-text' : ''}">
                        <p class="item-name">${escapeHtml(item.name)}</p>
                        ${item.subtitle ? `<p class="item-subtitle">${escapeHtml(item.subtitle)}</p>` : ''}
                    </div>
                </label>
                <button onclick="deleteItem(${item.id}, this)" class="delete-btn">
                    <span class="material-symbols-outlined" style="font-size: 1.25rem;">delete</span>
                </button>
            </div>
        `;

        if (isPurchased) {
            purchasedContainer.innerHTML += html;
        } else {
            activeContainer.innerHTML += html;
        }
    });

    countBadge.innerText = `${activeCount} produktów`;

    if (activeCount === 0 && activeContainer.innerHTML === '') {
        activeContainer.innerHTML = '<p class="empty-state-text">Wszystko kupione! 🎉</p>';
    }
}

function addItem() {
    if (!window.currentListId) {
        alert('Wybierz lub stwórz najpierw listę!');
        return;
    }

    const input = document.getElementById('new-item-name');
    const name = input.value.trim();
    if (!name) return;

    fetch(`/groups/${window.groupId}/lists/${window.currentListId}/items/add`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                loadList(window.currentListId, document.querySelector('.list-tab-active'));
            }
        });
}

function handleEnter(e) {
    if (e.key === 'Enter') addItem();
}

function toggleItem(itemId,currentStatus) {
    const nextStatus = !currentStatus;

    fetch(`/groups/${window.groupId}/items/${itemId}/toggle`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            isPurchased : nextStatus
        })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadList(window.currentListId, document.querySelector('.list-tab-active'));
            }
        });
}


function openDeleteModal(type, id, name, itemRow = null) {
    const modal = document.getElementById('delete-modal');
    const form = document.getElementById('delete-form-action');
    const targetText = document.getElementById('modal-delete-target');

    targetText.textContent = name;
    form.setAttribute('data-id', id);

    form.setAttribute('data-action-type', type);

    if (type === 'item' && itemRow) {
        form.itemRowToDelete = itemRow;
    } else {
        form.itemRowToDelete = null;
    }

    modal.classList.add('visible');
}

function deleteList(listId) {
    const list = window.allLists.find(l => l.id == listId);
    const name = list ? `listę zakupów "${list.name}"` : 'tę listę';
    openDeleteModal('list', listId, name);
}

function deleteItem(itemId, btn) {

    const itemRow = btn.closest('.shopping-item-row');

    const itemName = itemRow ? itemRow.querySelector('.item-name').textContent : 'ten produkt';

    openDeleteModal('item', itemId, `produkt "${itemName}"`, itemRow);
}



document.addEventListener('DOMContentLoaded', () => {

    const deleteModal = document.getElementById('delete-modal');
    const deleteForm = document.getElementById('delete-form-action');
    const deleteCancelButton = document.getElementById('modal-delete-cancel');

    if (deleteModal && deleteForm && deleteCancelButton) {

        deleteCancelButton.addEventListener('click', function() {
            deleteModal.classList.remove('visible');
        });
        deleteModal.addEventListener('click', function(event) {
            if (event.target === deleteModal) {
                deleteModal.classList.remove('visible');
            }
        });

        deleteForm.addEventListener('submit', function(event) {
            event.preventDefault();
            deleteModal.classList.remove('visible');

            const id = deleteForm.getAttribute('data-id');
            const type = deleteForm.getAttribute('data-action-type');

            if (!id) return;

            let url;
            let rowToRemove = deleteForm.itemRowToDelete;

            if (type === 'list') {
                url = `/groups/${window.groupId}/lists/${id}/delete`;
            } else if (type === 'item') {
                url = `/groups/${window.groupId}/items/${id}/delete`;
            } else {
                return;
            }

            fetch(url, { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (type === 'list') {

                            location.reload();
                        } else if (type === 'item') {

                            if (rowToRemove) {
                                rowToRemove.remove();
                            }
                            loadList(window.currentListId, document.querySelector('.list-tab-active'));
                        }
                    } else {
                        alert('Nie udało się usunąć elementu. Spróbuj ponownie.');
                    }
                })
                .catch(err => {
                    console.error('Błąd podczas usuwania:', err);
                    alert('Wystąpił błąd komunikacji z serwerem.');
                });
        });
    }
    const createModal = document.getElementById('create-list-modal');
    const createForm = document.getElementById('create-list-form');
    const createCancelButton = document.getElementById('modal-create-cancel');
    const createNameInput = document.getElementById('new-list-name');

    if (createModal && createForm && createCancelButton) {
        createCancelButton.addEventListener('click', function() {
            createModal.classList.remove('visible');
            createForm.reset();
        });
        createModal.addEventListener('click', function(event) {
            if (event.target === createModal) {
                createModal.classList.remove('visible');
                createForm.reset();
            }
        });

        createForm.addEventListener('submit', function(event) {
            event.preventDefault();
            createModal.classList.remove('visible');
            const name = createNameInput.value.trim();
            if (name) {
                fetch(`/groups/${window.groupId}/lists/add`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name: name })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Nie udało się utworzyć nowej listy. Spróbuj ponownie.');
                        }
                    })
                    .catch(err => console.error('Błąd podczas tworzenia listy:', err))
                    .finally(() => {
                        createForm.reset();
                    });
            }
        });
    }
});
function openNewListModal() {
    const modal = document.getElementById('create-list-modal');
    modal.classList.add('visible');
    const input = document.getElementById('new-list-name');
    if (input) {
        input.focus();
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}