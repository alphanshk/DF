<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Budget Expense Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Student Budget Expense Tracker</h1>

        <section class="summary-cards">
            <div class="card balance">
                <h3>Total Balance</h3>
                <p id="totalBalance">$0.00</p>
            </div>
            <div class="card income">
                <h3>Total Income</h3>
                <p id="totalIncome">$0.00</p>
            </div>
            <div class="card expense">
                <h3>Total Expense</h3>
                <p id="totalExpense">$0.00</p>
            </div>
        </section>

        <section class="form-section">
            <h2 id="formTitle">Add Transaction</h2>
            <form id="transactionForm">
                <input type="hidden" id="transactionId" value="">

                <div class="form-grid">
                    <div>
                        <label for="description">Description</label>
                        <input type="text" id="description" placeholder="Example: Library books" required>
                    </div>

                    <div>
                        <label for="amount">Amount</label>
                        <input type="number" id="amount" step="0.01" min="0.01" placeholder="Example: 25.50" required>
                    </div>

                    <div>
                        <label for="type">Type</label>
                        <select id="type" required>
                            <option value="">Select Type</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>

                    <div>
                        <label for="category">Category</label>
                        <select id="category" required>
                            <option value="">Select Category</option>
                            <option value="Food">Food</option>
                            <option value="Travel">Travel</option>
                            <option value="Study">Study</option>
                            <option value="Shopping">Shopping</option>
                            <option value="Bills">Bills</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="date">Date</label>
                        <input type="date" id="date" required>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" id="saveBtn">Add Transaction</button>
                    <button type="button" id="cancelEditBtn" class="secondary hidden">Cancel Edit</button>
                </div>
            </form>
            <p id="message"></p>
        </section>

        <section class="table-section">
            <h2>Transactions</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="transactionsBody">
                        <tr>
                            <td colspan="6" class="empty">No transactions yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
