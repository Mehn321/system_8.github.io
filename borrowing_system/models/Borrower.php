<?php

/**
 * Borrower Model Class
 * Handles borrower management operations
 */

require_once '../classes/Database.php';

class Borrower
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function create($data)
    {
        $id_number = $this->db->escape($data['id_number']);
        $full_name = $this->db->escape($data['full_name']);
        $department = $this->db->escape($data['department_course_office']);
        $contact = $this->db->escape($data['contact_number']);
        $email = $this->db->escape($data['email_address']);

        $sql = "INSERT INTO borrowers
                (id_number, full_name, department_course_office, contact_number, email_address)
                VALUES
                ('$id_number', '$full_name', '$department', '$contact', '$email')";

        if ($this->db->query($sql)) {
            return $this->db->insert_id();
        }

        return false;
    }

    public function getAll($search = '', $limit = 0, $offset = 0)
    {
        $search = $this->db->escape($search);

        $where_clause = '';
        if (!empty($search)) {
            $where_clause = "WHERE id_number LIKE '%$search%'
                           OR full_name LIKE '%$search%'
                           OR department_course_office LIKE '%$search%'
                           OR email_address LIKE '%$search%'";
        }

        $limit_clause = '';
        if ($limit > 0) {
            $offset = $this->db->escape($offset);
            $limit_clause = "LIMIT $offset, $limit";
        }

        $sql = "SELECT * FROM borrowers
                $where_clause
                ORDER BY full_name
                $limit_clause";

        $result = $this->db->query($sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getById($borrower_id)
    {
        $borrower_id = $this->db->escape($borrower_id);

        $sql = "SELECT * FROM borrowers WHERE borrower_id = $borrower_id";
        $result = $this->db->query($sql);

        return mysqli_fetch_assoc($result);
    }

    public function getByIdNumber($id_number)
    {
        $id_number = $this->db->escape($id_number);

        $sql = "SELECT * FROM borrowers WHERE id_number = '$id_number'";
        $result = $this->db->query($sql);

        return mysqli_fetch_assoc($result);
    }

    public function update($borrower_id, $data)
    {
        $borrower_id = $this->db->escape($borrower_id);
        $id_number = $this->db->escape($data['id_number']);
        $full_name = $this->db->escape($data['full_name']);
        $department = $this->db->escape($data['department_course_office']);
        $contact = $this->db->escape($data['contact_number']);
        $email = $this->db->escape($data['email_address']);

        $sql = "UPDATE borrowers SET
                id_number = '$id_number',
                full_name = '$full_name',
                department_course_office = '$department',
                contact_number = '$contact',
                email_address = '$email',
                updated_at = CURRENT_TIMESTAMP
                WHERE borrower_id = $borrower_id";

        return $this->db->query($sql);
    }

    public function delete($borrower_id)
    {
        $borrower_id = $this->db->escape($borrower_id);

        $sql = "DELETE FROM borrowers WHERE borrower_id = $borrower_id";
        return $this->db->query($sql);
    }

    public function getBorrowingHistory($borrower_id)
    {
        $borrower_id = $this->db->escape($borrower_id);

        $sql = "SELECT bt.*, bi.item_id, bi.quantity_required, bi.quantity_issued, bi.quantity_returned,
                       i.item_name, i.item_code
                FROM borrowing_transactions bt
                JOIN borrowed_items bi ON bt.transaction_id = bi.transaction_id
                JOIN items i ON bi.item_id = i.item_id
                WHERE bt.borrower_id = $borrower_id
                ORDER BY bt.created_at DESC";

        $result = $this->db->query($sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getCount()
    {
        $sql = "SELECT COUNT(*) as count FROM borrowers";
        $result = $this->db->query($sql);
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }

    /**
     * Get borrowers aggregated by department with transaction stats.
     * Returns keys: department_course_office, borrower_count, active_transactions, completed_transactions, overdue_transactions
     */
    public function getDepartmentStats()
    {
        $sql = "
            SELECT
                b.department_course_office,
                COUNT(DISTINCT b.borrower_id) AS borrower_count,
                SUM(CASE WHEN bt.status IN ('pending','approved','issued') THEN 1 ELSE 0 END) AS active_transactions,
                SUM(CASE WHEN bt.status = 'returned' THEN 1 ELSE 0 END) AS completed_transactions,
                SUM(CASE WHEN bt.status = 'issued' AND bt.date_of_return < CURDATE() THEN 1 ELSE 0 END) AS overdue_transactions
            FROM borrowers b
            LEFT JOIN borrowing_transactions bt ON bt.borrower_id = b.borrower_id
            GROUP BY b.department_course_office
            ORDER BY b.department_course_office
        ";

        $result = $this->db->query($sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
