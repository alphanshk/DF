/**
 * Smart Expense Tracker frontend interactions.
 */

(function () {
    const expenseForm = document.getElementById('expenseForm');
    const filterForm = document.getElementById('filterForm');
    const tableBody = document.getElementById('expenseTableBody');
    const paginationEl = document.getElementById('pagination');
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editExpenseForm');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const toastContainer = document.getElementById('toastContainer');
    const themeToggle = document.getElementById('themeToggle');

    let currentPage = 1;
    let chartRefs = { monthly: null, category: null };

    function showToast(message, type = 'success') {
        if (!toastContainer) return;
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);
        setTimeout(() => toast.remove(), 2800);
    }

    function serializeFilters() {
        if (!filterForm) return '';
        const params = new URLSearchParams(new FormData(filterForm));
        params.set('page', String(currentPage));
        return params.toString();
    }

    async function fetchExpenses() {
        if (!tableBody) return;

        const query = serializeFilters();
        try {
            const response = await fetch(`api/fetch_expenses.php?${query}`);
            const result = await response.json();

            if (!result.success) {
                showToast(result.message || 'Failed to fetch expenses', 'error');
                return;
            }

            renderExpenses(result.expenses || []);
            renderPagination(result.pagination);
            recalculateTotal(result.expenses || []);
        } catch (error) {
            showToast('Network error while loading expenses', 'error');
        }
    }

    function recalculateTotal(rows) {
        const totalEl = document.getElementById('totalExpenseValue');
        if (!totalEl) return;
        const pageTotal = rows.reduce((sum, row) => sum + parseFloat(row.amount), 0);
        if (rows.length > 0) {
            totalEl.textContent = `$${pageTotal.toFixed(2)} (page subtotal)`;
        }
    }

    function renderExpenses(expenses) {
        tableBody.innerHTML = '';

        if (!expenses.length) {
            tableBody.innerHTML = '<tr><td colspan="5">No expenses found.</td></tr>';
            return;
        }

        expenses.forEach((expense) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(expense.title)}</td>
                <td>$${Number(expense.amount).toFixed(2)}</td>
                <td>${escapeHtml(expense.category)}</td>
                <td>${escapeHtml(expense.date)}</td>
                <td>
                    <button class="btn btn-small btn-secondary edit-btn"
                        data-id="${expense.id}"
                        data-title="${encodeURIComponent(expense.title)}"
                        data-amount="${expense.amount}"
                        data-category="${expense.category}"
                        data-date="${expense.date}">Edit</button>
                    <button class="btn btn-small btn-danger delete-btn" data-id="${expense.id}">Delete</button>
                </td>
            `;
            tableBody.appendChild(tr);
        });
    }

    function renderPagination(pagination) {
        if (!paginationEl || !pagination) return;
        paginationEl.innerHTML = '';

        const { page, total_pages: totalPages } = pagination;
        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i += 1) {
            const btn = document.createElement('button');
            btn.className = `btn btn-small ${i === page ? 'btn-primary' : ''}`;
            btn.textContent = String(i);
            btn.addEventListener('click', () => {
                currentPage = i;
                fetchExpenses();
            });
            paginationEl.appendChild(btn);
        }
    }

    async function handleAddExpense(event) {
        event.preventDefault();
        const formData = new FormData(expenseForm);

        try {
            const response = await fetch('api/add_expense.php', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();

            if (!result.success) {
                showToast(result.message || 'Failed to add expense', 'error');
                return;
            }

            showToast(result.message || 'Expense added');
            expenseForm.reset();
            currentPage = 1;
            fetchExpenses();
        } catch (error) {
            showToast('Network error while adding expense', 'error');
        }
    }

    async function deleteExpense(id) {
        if (!confirm('Are you sure you want to delete this expense?')) return;

        const formData = new FormData();
        formData.append('id', id);

        try {
            const response = await fetch('api/delete_expense.php', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();

            if (!result.success) {
                showToast(result.message || 'Delete failed', 'error');
                return;
            }

            showToast(result.message || 'Expense deleted');
            fetchExpenses();
        } catch (error) {
            showToast('Network error while deleting expense', 'error');
        }
    }

    function openEditModal(data) {
        if (!editModal || !editForm) return;

        document.getElementById('editId').value = data.id;
        document.getElementById('editTitle').value = decodeURIComponent(data.title);
        document.getElementById('editAmount').value = data.amount;
        document.getElementById('editCategory').value = data.category;
        document.getElementById('editDate').value = data.date;
        editModal.classList.remove('hidden');
    }

    function closeEditModal() {
        if (editModal) editModal.classList.add('hidden');
    }

    async function handleEditExpense(event) {
        event.preventDefault();
        const formData = new FormData(editForm);

        try {
            const response = await fetch('api/update_expense.php', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();

            if (!result.success) {
                showToast(result.message || 'Update failed', 'error');
                return;
            }

            showToast(result.message || 'Expense updated');
            closeEditModal();
            fetchExpenses();
        } catch (error) {
            showToast('Network error while updating expense', 'error');
        }
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setupCharts() {
        if (!window.Chart || !window.__INITIAL_CHART_DATA__) return;

        const monthlyCanvas = document.getElementById('monthlyChart');
        const categoryCanvas = document.getElementById('categoryChart');

        if (monthlyCanvas) {
            const monthly = window.__INITIAL_CHART_DATA__.monthly || [];
            chartRefs.monthly = new Chart(monthlyCanvas, {
                type: 'line',
                data: {
                    labels: monthly.map((x) => x.month),
                    datasets: [{
                        label: 'Monthly Total ($)',
                        data: monthly.map((x) => Number(x.total)),
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79,70,229,0.12)',
                        fill: true,
                        tension: 0.3,
                    }],
                },
            });
        }

        if (categoryCanvas) {
            const categories = window.__INITIAL_CHART_DATA__.category || [];
            chartRefs.category = new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: categories.map((x) => x.category),
                    datasets: [{
                        data: categories.map((x) => Number(x.total)),
                        backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#6366f1'],
                    }],
                },
            });
        }
    }

    function setupThemeToggle() {
        if (!themeToggle) return;

        const storedTheme = localStorage.getItem('set_theme') || 'light';
        document.documentElement.setAttribute('data-theme', storedTheme);
        themeToggle.textContent = storedTheme === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';

        themeToggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('set_theme', next);
            themeToggle.textContent = next === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';
        });
    }

    function setupBasicFormValidation() {
        const forms = [document.getElementById('loginForm'), document.getElementById('registerForm')];
        forms.forEach((form) => {
            if (!form) return;
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    showToast('Please fill all required fields correctly.', 'error');
                }
            });
        });
    }

    if (expenseForm) {
        expenseForm.addEventListener('submit', handleAddExpense);
    }

    if (filterForm) {
        filterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            currentPage = 1;
            fetchExpenses();
        });
    }

    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', () => {
            filterForm.reset();
            currentPage = 1;
            fetchExpenses();
        });
    }

    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', () => {
            const params = new URLSearchParams(new FormData(filterForm));
            params.set('export', 'csv');
            window.location.href = `api/fetch_expenses.php?${params.toString()}`;
        });
    }

    if (tableBody) {
        tableBody.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) return;

            if (target.classList.contains('delete-btn')) {
                deleteExpense(target.dataset.id);
            }

            if (target.classList.contains('edit-btn')) {
                openEditModal(target.dataset);
            }
        });
    }

    if (editForm) {
        editForm.addEventListener('submit', handleEditExpense);
    }

    const closeModalBtn = document.getElementById('closeModal');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeEditModal);
    }

    if (editModal) {
        editModal.addEventListener('click', (event) => {
            if (event.target === editModal) closeEditModal();
        });
    }

    setupThemeToggle();
    setupBasicFormValidation();
    setupCharts();
    fetchExpenses();
})();
