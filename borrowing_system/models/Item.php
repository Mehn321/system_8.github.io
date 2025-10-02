<?php

/**
 * Item Model Class
 * Handles inventory management operations
 */

require_once '../classes/Database.php';

class Item
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function create($data)
    {
        $item_code = $this->db->escape($data['item_code']);
        $item_name = $this->db->escape($data['item_name']);
        $description = $this->db->escape($data['item_description']);
        $category = $this->db->escape($data['category']);
        $total_quantity = $this->db->escape($data['total_quantity']);
        $unit = $this->db->escape($data['unit']);

        $sql = "INSERT INTO items
                (item_code, item_name, item_description, category_id, total_quantity, available_quantity, unit)
                VALUES
                ('$item_code', '$item_name', '$description', '$category', $total_quantity, $total_quantity, '$unit')";

        if ($this->db->query($sql)) {
            return $this->db->insert_id();
        }

        return false;
    }

    public function getAll($search = '', $category = '', $limit = 0, $offset = 0)
    {
        $search = $this->db->escape($search);
        $category = $this->db->escape($category);

        $where_clause = '';
        $conditions = [];

        if (!empty($search)) {
            $conditions[] = "(i.item_code LIKE '%$search%' OR i.item_name LIKE '%$search%' OR i.item_description LIKE '%$search%')";
        }

        if (!empty($category)) {
            $conditions[] = "c.category_name = '$category'";
        }

        if (!empty($conditions)) {
            $where_clause = "WHERE " . implode(' AND ', $conditions);
        }

        $limit_clause = '';
        if ($limit > 0) {
            $offset = $this->db->escape($offset);
            $limit_clause = "LIMIT $offset, $limit";
        }

        $sql = "SELECT i.*, c.category_name as category FROM items i
                LEFT JOIN categories c ON i.category_id = c.category_id
                $where_clause
                ORDER BY i.item_name
                $limit_clause";

        $result = $this->db->query($sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getById($item_id)
    {
        $item_id = $this->db->escape($item_id);

        $sql = "SELECT * FROM items WHERE item_id = $item_id";
        $result = $this->db->query($sql);

        return mysqli_fetch_assoc($result);
    }

    public function getByCode($item_code)
    {
        $item_code = $this->db->escape($item_code);

        $sql = "SELECT * FROM items WHERE item_code = '$item_code'";
        $result = $this->db->query($sql);

        return mysqli_fetch_assoc($result);
    }

    public function update($item_id, $data)
    {
        $item_id = $this->db->escape($item_id);
        $item_code = $this->db->escape($data['item_code']);
        $item_name = $this->db->escape($data['item_name']);
        $description = $this->db->escape($data['item_description']);
        $category = $this->db->escape($data['category']);
        $total_quantity = $this->db->escape($data['total_quantity']);
        $unit = $this->db->escape($data['unit']);

        // Get current item to calculate available quantity adjustment
        $current_item = $this->getById($item_id);
        $quantity_diff = $total_quantity - $current_item['total_quantity'];
        $new_available = $current_item['available_quantity'] + $quantity_diff;

        $sql = "UPDATE items SET
                item_code = '$item_code',
                item_name = '$item_name',
                item_description = '$description',
                category_id = '$category',
                total_quantity = $total_quantity,
                available_quantity = $new_available,
                unit = '$unit',
                updated_at = CURRENT_TIMESTAMP
                WHERE item_id = $item_id";

        return $this->db->query($sql);
    }

    public function delete($item_id)
    {
        $item_id = $this->db->escape($item_id);

        // Check if item is currently borrowed
        $sql = "SELECT COUNT(*) as count FROM borrowed_items
                WHERE item_id = $item_id AND quantity_returned < quantity_issued";

        $result = $this->db->query($sql);
        $row = mysqli_fetch_assoc($result);

        if ($row['count'] > 0) {
            return false; // Cannot delete item that is currently borrowed
        }

        $sql = "DELETE FROM items WHERE item_id = $item_id";
        return $this->db->query($sql);
    }

    public function getCategories()
    {
        $sql = "SELECT DISTINCT c.category_name FROM items i LEFT JOIN categories c ON i.category_id = c.category_id ORDER BY c.category_name";
        $result = $this->db->query($sql);

        $categories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row['category_name'];
        }

        return $categories;
    }

    public function checkAvailability($item_id, $requested_quantity)
    {
        $item = $this->getById($item_id);

        if (!$item) {
            return false;
        }

        return $item['available_quantity'] >= $requested_quantity;
    }

    public function getLowStockItems($threshold = 5)
    {
        $sql = "SELECT * FROM items
                WHERE available_quantity <= $threshold
                ORDER BY available_quantity ASC";

        $result = $this->db->query($sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getCount()
    {
        $sql = "SELECT COUNT(*) as count FROM items";
        $result = $this->db->query($sql);
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }

    public function getMostBorrowed($limit = 10)
    {
        $sql = "SELECT i.*, c.category_name as category, COUNT(bi.item_id) as borrow_count
                FROM items i
                LEFT JOIN categories c ON i.category_id = c.category_id
                LEFT JOIN borrowed_items bi ON i.item_id = bi.item_id
                GROUP BY i.item_id
                ORDER BY borrow_count DESC
                LIMIT $limit";

        $result = $this->db->query($sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
