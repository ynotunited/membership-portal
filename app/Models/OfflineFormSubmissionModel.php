<?php
namespace App\Models;

use App\Models\BaseModel;

class OfflineFormSubmissionModel extends BaseModel
{
    protected $table = 'offline_form_submissions';

    /**
     * Create a new offline form submission
     */
    public function createSubmission($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (
                reference_number, filename, filepath, file_size, file_type, 
                submitted_at, status, email, phone
            ) VALUES (
                :reference_number, :filename, :filepath, :file_size, :file_type,
                :submitted_at, :status, :email, :phone
            )";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'reference_number' => $data['reference_number'],
                'filename' => $data['filename'],
                'filepath' => $data['filepath'],
                'file_size' => $data['file_size'] ?? null,
                'file_type' => $data['file_type'] ?? null,
                'submitted_at' => $data['submitted_at'],
                'status' => $data['status'] ?? 'pending_review',
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null
            ]);
            
            if ($result) {
                return [
                    'success' => true,
                    'id' => $this->db->lastInsertId(),
                    'message' => 'Submission created successfully'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to create submission'
            ];
            
        } catch (\Exception $e) {
            error_log('Error creating offline form submission: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error occurred'
            ];
        }
    }

    /**
     * Get submission by reference number
     */
    public function getByReferenceNumber($referenceNumber)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE reference_number = :reference_number";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['reference_number' => $referenceNumber]);
            
            return $stmt->fetch();
            
        } catch (\Exception $e) {
            error_log('Error getting submission by reference: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all submissions with optional filtering
     */
    public function getAllSubmissions($filters = [])
    {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $params = [];
            $whereClauses = [];
            
            // Add filters
            if (!empty($filters['status'])) {
                $whereClauses[] = "status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($filters['date_from'])) {
                $whereClauses[] = "submitted_at >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereClauses[] = "submitted_at <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }
            
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }
            
            $sql .= " ORDER BY submitted_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (\Exception $e) {
            error_log('Error getting all submissions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update submission status
     */
    public function updateStatus($id, $status, $reviewerId = null, $notes = null)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                status = :status,
                reviewed_by = :reviewer_id,
                reviewed_at = NOW(),
                review_notes = :notes
                WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'status' => $status,
                'reviewer_id' => $reviewerId,
                'notes' => $notes,
                'id' => $id
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            error_log('Error updating submission status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Link submission to member account
     */
    public function linkToMember($submissionId, $memberId)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                member_id = :member_id,
                status = 'processed'
                WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'member_id' => $memberId,
                'id' => $submissionId
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            error_log('Error linking submission to member: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get submission statistics
     */
    public function getStatistics()
    {
        try {
            $sql = "SELECT 
                status,
                COUNT(*) as count,
                DATE(submitted_at) as date
                FROM {$this->table}
                WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY status, DATE(submitted_at)
                ORDER BY date DESC, status";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (\Exception $e) {
            error_log('Error getting submission statistics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete submission (admin only)
     */
    public function deleteSubmission($id)
    {
        try {
            // First get the file path to delete the physical file
            $submission = $this->getById($id);
            if ($submission && file_exists($submission['filepath'])) {
                unlink($submission['filepath']);
            }
            
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(['id' => $id]);
            
            return $result;
            
        } catch (\Exception $e) {
            error_log('Error deleting submission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get submission by ID
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch();
            
        } catch (\Exception $e) {
            error_log('Error getting submission by ID: ' . $e->getMessage());
            return false;
        }
    }
} 