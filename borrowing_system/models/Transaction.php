<?php

/**
 * Transaction Model Class
 * Handles borrowing transaction operations
 */

require_once '../classes/Database.php';

class Transaction
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function create($data)
    {
        $borrower_id = $this->db->escape($data['borrower_id']);
        $activity_purpose = $this->db->escape($data['activity_purpose']);
        $place_of_activity = $this->db->escape($data['place_of_activity']);
        $date_requested = $this->db->escape($data['date_requested']);
        $date_needed = $this->db->escape($data['date_needed']);
        $date_of_return = $this->db->escape($data['date_of_return']);

        $sql = "INSERT INTO borrowing_transactions
                (borrower_id, activity_purpose, place_of_activity, date_requested, date_needed, date_of_return)
                VALUES
                ($borrower_id, '$activity_purpose', '$place_of_activity', '$date_requested', '$date_needed', '$date_of_return')";

        if ($this->db->query($sql)) {
            return $this->db->insert_id();
        }

        return false;
    }

    public function addItem($transaction_id, $item_id, $quantity_required)
    {
        $transaction_id = $this->db->escape($transaction_id);
        $item_id = $this->db->escape($item_id);
        $quantity_required = $this->db->escape($quantity_required);

        $sql = "INSERT INTO borrowed_items
                (transaction_id, item_id, quantity_required, quantity_issued)
                VALUES
                ($transaction_id, $item_id, $quantity_required, 0)";

        return $this->db->query($sql);
    }

    public function getAll($filters = [], $limit = 0, $offset = 0)
    {
        $where_clause = '';
        $conditions = [];

        // Exclude returned transactions by default unless specifically requested
        if (!isset($filters['include_returned']) || $filters['include_returned'] !== true) {
            $conditions[] = "bt.status != 'returned'";
        }

        if (!empty($filters['search'])) {
            $search = $this->db->escape($filters['search']);
            $conditions[] = "(bt.activity_purpose LIKE '%$search%' OR b.full_name LIKE '%$search%' OR b.id_number LIKE '%$search%')";
        }

        if (!empty($filters['status'])) {
            $status = $this->db->escape($filters['status']);
            $conditions[] = "bt.status = '$status'";
        }

        if (!empty($filters['department'])) {
            $department = $this->db->escape($filters['department']);
            $conditions[] = "b.department_course_office = '$department'";
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $date_from = $this->db->escape($filters['date_from']);
            $date_to = $this->db->escape($filters['date_to']);
            $conditions[] = "bt.date_requested BETWEEN '$date_from' AND '$date_to'";
        }

        if (!empty($conditions)) {
            $where_clause = "WHERE " . implode(' AND ', $conditions);
        }

        $limit_clause = '';
        if ($limit > 0) {
            $offset = $this->db->escape($offset);
            $limit_clause = "LIMIT $offset, $limit";
        }

        $sql = "SELECT bt.*, b.full_name, b.id_number, b.department_course_office,
                       b.contact_number, b.email_address
                FROM borrowing_transactions bt
                JOIN borrowers b ON bt.borrower_id = b.borrower_id
                $where_clause
                ORDER BY bt.created_at DESC
                $limit_clause";

        $result = $this->db->query($sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getById($transaction_id)
    {
        $transaction_id = $this->db->escape($transaction_id);

        $sql = "SELECT bt.*, b.full_name, b.id_number, b.department_course_office,
                       b.contact_number, b.email_address
                FROM borrowing_transactions bt
                JOIN borrowers b ON bt.borrower_id = b.borrower_id
                WHERE bt.transaction_id = $transaction_id";

        $result = $this->db->query($sql);
        return mysqli_fetch_assoc($result);
    }

    public function getItems($transaction_id)
    {
        $transaction_id = $this->db->escape($transaction_id);

        $sql = "SELECT bi.*, i.item_name, i.item_code, i.unit
                FROM borrowed_items bi
                JOIN items i ON bi.item_id = i.item_id
                WHERE bi.transaction_id = $transaction_id";

        $result = $this->db->query($sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function approve($transaction_id, $admin_id)
    {
        $transaction_id = $this->db->escape($transaction_id);
        $admin_id = $this->db->escape($admin_id);

        $sql = "UPDATE borrowing_transactions
                SET status = 'approved', updated_at = CURRENT_TIMESTAMP
                WHERE transaction_id = $transaction_id";

        if ($this->db->query($sql)) {
            // Add approval record
            $this->addApproval($transaction_id, 'issued_by', $admin_id);
            return true;
        }

        return false;
    }

    public function issueItems($transaction_id, $admin_id)
    {
        $transaction_id = $this->db->escape($transaction_id);
        $admin_id = $this->db->escape($admin_id);

        // Get all items in this transaction
        $items = $this->getItems($transaction_id);

        foreach ($items as $item) {
            // Check if quantity is available
            $item_model = new Item();
            if (!$item_model->checkAvailability($item['item_id'], $item['quantity_required'])) {
                return false; // Not enough stock
            }
        }

        // Issue all items (update quantities)
        foreach ($items as $item) {
            $sql = "UPDATE borrowed_items
                    SET quantity_issued = quantity_required
                    WHERE borrowed_item_id = " . $item['borrowed_item_id'];

            $this->db->query($sql);
        }

        // Update transaction status
        $sql = "UPDATE borrowing_transactions
                SET status = 'issued', updated_at = CURRENT_TIMESTAMP
                WHERE transaction_id = $transaction_id";

        if ($this->db->query($sql)) {
            // Add approval record
            $this->addApproval($transaction_id, 'assessed_received_by', $admin_id);
            return true;
        }

        return false;
    }

    public function returnItems($transaction_id, $quantities_returned, $admin_id)
    {
        $transaction_id = $this->db->escape($transaction_id);
        $admin_id = $this->db->escape($admin_id);

        if (!is_array($quantities_returned)) {
            return false;
        }

        // Update returned quantities
        foreach ($quantities_returned as $borrowed_item_id => $quantity) {
            $borrowed_item_id = $this->db->escape($borrowed_item_id);
            $quantity = $this->db->escape($quantity);

            $sql = "UPDATE borrowed_items
                    SET quantity_returned = quantity_returned + $quantity
                    WHERE borrowed_item_id = $borrowed_item_id";

            $this->db->query($sql);
        }

        // Check if all items are returned
        $items = $this->getItems($transaction_id);
        $all_returned = true;

        foreach ($items as $item) {
            if ($item['quantity_returned'] < $item['quantity_issued']) {
                $all_returned = false;
                break;
            }
        }

        // Update transaction status
        $new_status = $all_returned ? 'returned' : 'issued';
        $sql = "UPDATE borrowing_transactions
                SET status = '$new_status', updated_at = CURRENT_TIMESTAMP
                WHERE transaction_id = $transaction_id";

        if ($this->db->query($sql)) {
            // Add approval record
            $this->addApproval($transaction_id, 'noted_by', $admin_id);
            return true;
        }

        return false;
    }

    private function addApproval($transaction_id, $approval_type, $admin_id)
    {
        $transaction_id = $this->db->escape($transaction_id);
        $approval_type = $this->db->escape($approval_type);
        $admin_id = $this->db->escape($admin_id);

        $sql = "INSERT INTO approvals
                (transaction_id, approval_type, admin_id)
                VALUES
                ($transaction_id, '$approval_type', $admin_id)";

        return $this->db->query($sql);
    }

    public function getDashboardStats()
    {
        $stats = [];

        // Today's borrowed items
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as count FROM borrowing_transactions
                WHERE DATE(created_at) = '$today' AND status IN ('issued', 'returned')";
        $result = $this->db->query($sql);
        $stats['today_borrowed'] = mysqli_fetch_assoc($result)['count'];

        // This week's borrowed items
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $sql = "SELECT COUNT(*) as count FROM borrowing_transactions
                WHERE created_at >= '$week_start' AND status IN ('issued', 'returned')";
        $result = $this->db->query($sql);
        $stats['week_borrowed'] = mysqli_fetch_assoc($result)['count'];

        // This month's borrowed items
        $month_start = date('Y-m-01');
        $sql = "SELECT COUNT(*) as count FROM borrowing_transactions
                WHERE created_at >= '$month_start' AND status IN ('issued', 'returned')";
        $result = $this->db->query($sql);
        $stats['month_borrowed'] = mysqli_fetch_assoc($result)['count'];

        // Overdue items
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as count FROM borrowing_transactions
                WHERE date_of_return < '$today' AND status = 'issued'";
        $result = $this->db->query($sql);
        $stats['overdue'] = mysqli_fetch_assoc($result)['count'];

        // Due within 3 days
        $three_days_later = date('Y-m-d', strtotime('+3 days'));
        $sql = "SELECT COUNT(*) as count FROM borrowing_transactions
                WHERE date_of_return BETWEEN '$today' AND '$three_days_later' AND status = 'issued'";
        $result = $this->db->query($sql);
        $stats['due_soon'] = mysqli_fetch_assoc($result)['count'];

        return $stats;
    }

    public function getCount()
    {
        $sql = "SELECT COUNT(*) as count FROM borrowing_transactions";
        $result = $this->db->query($sql);
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }

    /**
     * Get monthly activity report aggregates.
     * Returns an array of rows with keys: month (YYYY-MM), total_transactions, items_borrowed, items_returned, overdue
     */
    public function getMonthlyReport()
    {
        // Aggregate transactions per month
        $sql = "
            SELECT
                DATE_FORMAT(bt.created_at, '%Y-%m') AS month,
                COUNT(DISTINCT bt.transaction_id) AS total_transactions,
                COALESCE(SUM(bi.quantity_issued), 0) AS items_borrowed,
                COALESCE(SUM(bi.quantity_returned), 0) AS items_returned,
                SUM(
                    CASE
                        WHEN bt.status = 'issued' AND bt.date_of_return < CURDATE() THEN 1
                        ELSE 0
                    END
                ) AS overdue
            FROM borrowing_transactions bt
            LEFT JOIN borrowed_items bi ON bt.transaction_id = bi.transaction_id
            GROUP BY DATE_FORMAT(bt.created_at, '%Y-%m')
            ORDER BY month DESC
        ";

        $result = $this->db->query($sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    /**
     * Get list of overdue items with borrower and item details.
     */
    public function getOverdueItems()
    {
        $sql = "
            SELECT
                bt.transaction_id,
                b.full_name AS borrower_name,
                i.item_name,
                bt.created_at AS date_issued,
                bt.date_of_return,
                DATEDIFF(CURDATE(), bt.date_of_return) AS days_overdue,
                b.contact_number
            FROM borrowing_transactions bt
            JOIN borrowers b ON bt.borrower_id = b.borrower_id
            JOIN borrowed_items bi ON bt.transaction_id = bi.transaction_id
            JOIN items i ON bi.item_id = i.item_id
            WHERE bt.status = 'issued' AND bt.date_of_return < CURDATE()
            ORDER BY bt.date_of_return ASC
        ";

        $result = $this->db->query($sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
