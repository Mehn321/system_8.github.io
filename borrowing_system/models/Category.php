<?php

/**
 * Category Model Class
 * Handles category management operations
 */

require_once '../classes/Database.php';

class Category
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function create($data)
    {
        $category_name = $this->db->escape($data['category_name']);

        $sql = "INSERT INTO categories (category_name) VALUES ('$category_name')";

        if ($this->db->query($sql)) {
            return $this->db->insert_id();
        }

        return false;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM categories ORDER BY category_name";
        $result = $this->db->query($sql);

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getById($category_id)
    {
        $category_id = $this->db->escape($category_id);

        $sql = "SELECT * FROM categories WHERE category_id = $category_id";
        $result = $this->db->query($sql);

        return mysqli_fetch_assoc($result);
    }

    public function update($category_id, $data)
    {
        $category_id = $this->db->escape($category_id);
        $category_name = $this->db->escape($data['category_name']);

        $sql = "UPDATE categories SET category_name = '$category_name', updated_at = CURRENT_TIMESTAMP WHERE category_id = $category_id";

        return $this->db->query($sql);
    }

    public function delete($category_id)
    {
        $category_id = $this->db->escape($category_id);

        // Check if any items belong to this category
        $sql = "SELECT COUNT(*) as count FROM items WHERE category_id = $category_id";
        $result = $this->db->query($sql);
        $row = mysqli_fetch_assoc($result);

        if ($row['count'] > 0) {
            return false; // Cannot delete category with items
        }

        $sql = "DELETE FROM categories WHERE category_id = $category_id";
        return $this->db->query($sql);
    }
}
