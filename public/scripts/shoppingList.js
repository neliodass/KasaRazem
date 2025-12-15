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
        const isPurchased = item.is_purchased;
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

function deleteItem(itemId, btn) {
    if(!confirm('Usunąć ten produkt?')) return;

    fetch(`/groups/${window.groupId}/items/${itemId}/delete`, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = btn.closest('.shopping-item-row');
                row.remove();
            }
        });
}

function openNewListModal() {
    const name = prompt("Podaj nazwę nowej listy:");
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
                }
            });
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