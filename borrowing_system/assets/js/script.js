function toggleNav() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const mainContent = document.getElementById('mainContent');

    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');

    // For desktop, shift main content
    if (window.innerWidth > 768) {
        mainContent.classList.toggle('shifted');
    }
}

// Close sidebar when clicking overlay
document.getElementById('sidebarOverlay').addEventListener('click', toggleNav);

function editItem(id) {
    const items = window.itemsList || [];
    const item = items.find(i => i.item_id == id);
    if (!item) return;

    document.getElementById('edit_item_id').value = item.item_id;
    document.getElementById('edit_item_code').value = item.item_code;
    document.getElementById('edit_item_name').value = item.item_name;
    document.getElementById('edit_category').value = item.category_id;
    document.getElementById('edit_total_quantity').value = item.total_quantity;
    document.getElementById('edit_unit').value = item.unit;
    document.getElementById('edit_item_description').value = item.item_description;

    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function(event) {
    var modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

function editBorrower(id) {
    const borrowers = window.borrowersList || [];
    const borrower = borrowers.find(b => b.borrower_id == id);
    if (!borrower) return;

    // Create and show a modal form for editing borrower details
    let modal = document.getElementById('editBorrowerModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'editBorrowerModal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close" onclick="closeBorrowerModal()">&times;</span>
                <h3>Edit Borrower</h3>
                <form method="POST" action="">
                    <input type="hidden" id="edit_borrower_id" name="borrower_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_id_number">ID Number</label>
                            <input type="text" id="edit_id_number" name="id_number" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_full_name">Full Name</label>
                            <input type="text" id="edit_full_name" name="full_name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_department_course_office">Department/Course/Office</label>
                            <input type="text" id="edit_department_course_office" name="department_course_office" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_contact_number">Contact Number</label>
                            <input type="text" id="edit_contact_number" name="contact_number" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_email_address">Email Address</label>
                            <input type="email" id="edit_email_address" name="email_address" required>
                        </div>
                    </div>
                    <button type="submit" name="edit_borrower" class="btn btn-primary">Update Borrower</button>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
    }

    document.getElementById('edit_borrower_id').value = borrower.borrower_id;
    document.getElementById('edit_id_number').value = borrower.id_number;
    document.getElementById('edit_full_name').value = borrower.full_name;
    document.getElementById('edit_department_course_office').value = borrower.department_course_office;
    document.getElementById('edit_contact_number').value = borrower.contact_number;
    document.getElementById('edit_email_address').value = borrower.email_address;

    modal.style.display = 'block';
}

function closeBorrowerModal() {
    const modal = document.getElementById('editBorrowerModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('editBorrowerModal');
    if (modal && event.target == modal) {
        modal.style.display = 'none';
    }
});

// Modal popup for dashboard stat cards
function closeStatModal() {
    const modal = document.getElementById('statModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function showStatModal(title, content) {
    const modal = document.getElementById('statModal');
    if (!modal) return;
    document.getElementById('modalTitle').textContent = title;
    const modalContent = document.getElementById('modalContent');
    modalContent.innerHTML = content;
    modal.style.display = 'block';
}

function getStatDetails(type) {
    switch(type) {
        case 'total_transactions':
            return {
                title: 'Total Transactions',
                content: '<p>View all transactions including returned ones. <a href="transactions.php?status=returned">Click here to view returned transactions</a>.</p>'
            };
        case 'today_borrowed':
            return {
                title: "Today's Borrowed",
                content: '<p>Number of items borrowed today.</p>'
            };
        case 'week_borrowed':
            return {
                title: 'This Week',
                content: '<p>Number of items borrowed this week.</p>'
            };
        case 'month_borrowed':
            return {
                title: 'This Month',
                content: '<p>Number of items borrowed this month.</p>'
            };
        case 'overdue':
            return {
                title: 'Overdue Items',
                content: '<p>Items that are past their due date.</p>'
            };
        case 'due_soon':
            return {
                title: 'Due Soon',
                content: '<p>Items that are due within the next 3 days.</p>'
            };
        case 'total_borrowers':
            return {
                title: 'Total Borrowers',
                content: '<p>Registered borrowers. <a href="borrowers.php">Click here to view all borrowers</a>.</p>'
            };
        case 'total_items':
            return {
                title: 'Total Items',
                content: '<p>Inventory items. <a href="items.php">Click here to view all items</a>.</p>'
            };
        case 'low_stock':
            return {
                title: 'Low Stock Items',
                content: '<p>Items with low stock (5 or less).</p>'
            };
        case 'transaction_history':
            return {
                title: 'Transaction History',
                content: '<p>View all transactions including returned ones. <a href="transaction_history.php">Click here to view transaction history</a>.</p>'
            };
        default:
            return {
                title: 'Details',
                content: '<p>No additional information available.</p>'
            };
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const statCards = document.querySelectorAll('.stat-card.clickable');
    statCards.forEach(card => {
        card.addEventListener('click', function() {
            const type = card.getAttribute('data-type');
            if (type === 'transaction_history' || type === 'total_transactions') {
                const url = type === 'transaction_history' ? 'transaction_history.php' : 'transactions.php';
                window.location.href = url;
            } else {
                const details = getStatDetails(type);
                showStatModal(details.title, details.content);
            }
        });
    });

    // Close modal when clicking outside modal content
    const modal = document.getElementById('statModal');
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeStatModal();
            }
        });
    }
});

function showReturnForm(transactionId) {
    document.getElementById('returnModal').style.display = 'block';
    document.getElementById('returnTransactionId').value = transactionId;

    const itemsList = document.getElementById('returnItemsList');
    itemsList.innerHTML = '<p>Loading transaction items...</p>';

    fetch(`../api/get_transaction_items.php?transaction_id=${transactionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                itemsList.innerHTML = `<p>Error: ${data.error}</p>`;
                return;
            }
            if (data.length === 0) {
                itemsList.innerHTML = '<p>No items found for this transaction.</p>';
                return;
            }

            let html = '';
            data.forEach(item => {
                const maxReturnable = item.quantity_issued - item.quantity_returned;
                html += `
                    <div style="margin-bottom: 10px;">
                        <label>
                            <strong>${item.item_name}</strong> (Issued: ${item.quantity_issued}, Returned: ${item.quantity_returned})<br>
                            Quantity to return:
                            <input type="number" name="quantities_returned[${item.borrowed_item_id}]" min="0" max="${maxReturnable}" value="${maxReturnable}" required>
                        </label>
                    </div>
                `;
            });
            itemsList.innerHTML = html;
        })
        .catch(error => {
            itemsList.innerHTML = `<p>Error loading items: ${error.message}</p>`;
        });
}

function hideReturnForm() {
    document.getElementById('returnModal').style.display = 'none';
}
