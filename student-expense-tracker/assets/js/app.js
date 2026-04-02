const form = document.getElementById('transactionForm');
const transactionId = document.getElementById('transactionId');
const description = document.getElementById('description');
const amount = document.getElementById('amount');
const type = document.getElementById('type');
const category = document.getElementById('category');
const date = document.getElementById('date');
const message = document.getElementById('message');
const transactionsBody = document.getElementById('transactionsBody');
const totalBalance = document.getElementById('totalBalance');
const totalIncome = document.getElementById('totalIncome');
const totalExpense = document.getElementById('totalExpense');
const formTitle = document.getElementById('formTitle');
const saveBtn = document.getElementById('saveBtn');
const cancelEditBtn = document.getElementById('cancelEditBtn');

const API_BASE = 'api';

date.valueAsDate = new Date();

function showMessage(text, isError = false) {
    message.textContent = text;
    message.style.color = isError ? '#d93025' : '#1e8e3e';
}

function resetForm() {
    form.reset();
    transactionId.value = '';
    formTitle.textContent = 'Add Transaction';
    saveBtn.textContent = 'Add Transaction';
    cancelEditBtn.classList.add('hidden');
    date.valueAsDate = new Date();
}

function validateInputs() {
    if (!description.value.trim() || !amount.value || !type.value || !category.value || !date.value) {
        showMessage('Please fill all fields.', true);
        return false;
    }

    if (Number(amount.value) <= 0 || Number.isNaN(Number(amount.value))) {
        showMessage('Amount must be greater than 0.', true);
        return false;
    }

    return true;
}

function formatCurrency(value) {
    return `$${Number(value).toFixed(2)}`;
}

async function fetchTransactions() {
    try {
        const response = await fetch(`${API_BASE}/fetch.php`);
        const data = await response.json();

        if (!data.success) {
            showMessage('Failed to load transactions.', true);
            return;
        }

        renderTransactions(data.transactions);
        renderSummary(data.summary);
    } catch (error) {
        showMessage('Unable to connect to server.', true);
    }
}

function renderSummary(summary) {
    totalIncome.textContent = formatCurrency(summary.income);
    totalExpense.textContent = formatCurrency(summary.expense);
    totalBalance.textContent = formatCurrency(summary.balance);

    if (Number(summary.balance) < 0) {
        totalBalance.style.color = '#d93025';
    } else {
        totalBalance.style.color = '#1e8e3e';
    }
}

function renderTransactions(transactions) {
    if (!transactions.length) {
        transactionsBody.innerHTML = '<tr><td colspan="6" class="empty">No transactions yet.</td></tr>';
        return;
    }

    transactionsBody.innerHTML = transactions.map((item) => `
        <tr>
            <td>${item.description}</td>
            <td class="${item.type === 'income' ? 'income-text' : 'expense-text'}">${formatCurrency(item.amount)}</td>
            <td>${item.type}</td>
            <td>${item.category}</td>
            <td>${item.date}</td>
            <td>
                <div class="action-buttons">
                    <button class="edit" onclick='startEdit(${JSON.stringify(item)})'>Edit</button>
                    <button class="delete" onclick="deleteTransaction(${item.id})">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
}

window.startEdit = function (item) {
    transactionId.value = item.id;
    description.value = item.description;
    amount.value = item.amount;
    type.value = item.type;
    category.value = item.category;
    date.value = item.date;

    formTitle.textContent = 'Edit Transaction';
    saveBtn.textContent = 'Update Transaction';
    cancelEditBtn.classList.remove('hidden');

    window.scrollTo({ top: 0, behavior: 'smooth' });
};

async function saveTransaction(event) {
    event.preventDefault();

    if (!validateInputs()) {
        return;
    }

    const payload = {
        description: description.value.trim(),
        amount: Number(amount.value),
        type: type.value,
        category: category.value,
        date: date.value
    };

    const isUpdate = Boolean(transactionId.value);

    if (isUpdate) {
        payload.id = Number(transactionId.value);
    }

    try {
        const response = await fetch(`${API_BASE}/${isUpdate ? 'update' : 'add'}.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            showMessage(data.message || 'Something went wrong.', true);
            return;
        }

        showMessage(data.message);
        resetForm();
        fetchTransactions();
    } catch (error) {
        showMessage('Unable to connect to server.', true);
    }
}

window.deleteTransaction = async function (id) {
    const shouldDelete = confirm('Are you sure you want to delete this transaction?');

    if (!shouldDelete) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/delete.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            showMessage(data.message || 'Failed to delete transaction.', true);
            return;
        }

        showMessage(data.message);
        fetchTransactions();
    } catch (error) {
        showMessage('Unable to connect to server.', true);
    }
};

form.addEventListener('submit', saveTransaction);
cancelEditBtn.addEventListener('click', () => {
    resetForm();
    showMessage('Edit canceled.');
});

fetchTransactions();
