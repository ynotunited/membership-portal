<?php
namespace App\Models;

class MembershipTypeModel extends BaseModel
{
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM membership_types ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM membership_types WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function add($type, $amount)
    {
        $stmt = $this->db->prepare("INSERT INTO membership_types (type, amount) VALUES (:type, :amount)");
        return $stmt->execute([
            'type' => $type,
            'amount' => $amount
        ]);
    }

    public function update($id, $type, $amount)
    {
        $stmt = $this->db->prepare("UPDATE membership_types SET type = :type, amount = :amount WHERE id = :id");
        return $stmt->execute([
            'type' => $type,
            'amount' => $amount,
            'id' => $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM membership_types WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
} 