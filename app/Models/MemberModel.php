<?php
namespace App\Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MemberModel extends BaseModel
{
    public function getAllMembers()
    {
        $stmt = $this->getConnection()->query("SELECT * FROM members ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function getMembersByDateRange($fromDate, $toDate)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT m.*, mt.type AS membership_type_name
            FROM members m
            LEFT JOIN membership_types mt ON m.membership_type = mt.id
            WHERE m.created_at BETWEEN :from_date AND :to_date
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([
            'from_date' => $fromDate . ' 00:00:00',
            'to_date' => $toDate . ' 23:59:59'
        ]);
        return $stmt->fetchAll();
    }

    public function getMember($id)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM members WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getMembersByIds(array $ids)
    {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM members WHERE id IN ($placeholders) ORDER BY id DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll();
    }

    public function addMember($data, $file = null)
    {
        // Unique email check
        $stmt = $this->getConnection()->prepare("SELECT id FROM members WHERE email = :email");
        $stmt->execute(['email' => $data['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already exists.'];
        }

        // Unique phone check
        $phone = $data['phone_country_code'] . $data['contact_number'];
        $stmt = $this->getConnection()->prepare("SELECT id FROM members WHERE contact_number = :phone");
        $stmt->execute(['phone' => $phone]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Phone number already exists.'];
        }

        // Password validation
        if (empty($data['password'])) {
            return ['success' => false, 'message' => 'Password is required.'];
        }
        if (strlen($data['password']) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Handle file uploads
        $photo = $this->handlePhotoUpload($file);
        $nin_card = $this->handleFileUpload($_FILES['nin_card'] ?? null, 'nin_cards', 'default_nin.jpg');
        $signature = $this->handleFileUpload($_FILES['signature'] ?? null, 'signatures', 'default_signature.jpg');

        // Generate unique membership number using robust checking
        $membershipNumber = $this->generateUniqueMembershipNumber();

        $sql = "INSERT INTO members (
            title, surname, firstname, othername, gender, marital_status, email, contact_number, whatsapp_number, dob,
            house_no, street_name, nearest_bus_stop, city_town, lga, state_district, country,
            business_name, business_address, nature_of_business, sub_sector,
            identity_type, id_number, date_of_issue, registration_status,
            chapter, zone, member_type, payment_type, membership_number, photo,
            password, payment_status, account_name, account_number, bank_name,
            nin_card, signature, created_at
        ) VALUES (
            :title, :surname, :firstname, :othername, :gender, :marital_status, :email, :contact_number, :whatsapp_number, :dob,
            :house_no, :street_name, :nearest_bus_stop, :city_town, :lga, :state_district, :country,
            :business_name, :business_address, :nature_of_business, :sub_sector,
            :identity_type, :id_number, :date_of_issue, :registration_status,
            :chapter, :zone, :member_type, :payment_type, :membership_number, :photo,
            :password, :payment_status, :account_name, :account_number, :bank_name,
            :nin_card, :signature, NOW()
        )";

        $stmt = $this->getConnection()->prepare($sql);
        $ok = $stmt->execute([
            'title' => $data['title'],
            'surname' => $data['surname'],
            'firstname' => $data['firstname'],
            'othername' => $data['othername'],
            'gender' => $data['gender'],
            'marital_status' => $data['marital_status'],
            'email' => $data['email'],
            'contact_number' => $phone,
            'whatsapp_number' => $data['whatsapp_country_code'] . $data['whatsapp_number'],
            'dob' => $data['dob'],
            'house_no' => $data['house_no'],
            'street_name' => $data['street_name'],
            'nearest_bus_stop' => $data['nearest_bus_stop'],
            'city_town' => $data['city_town'],
            'lga' => $data['lga'],
            'state_district' => $data['state_district'],
            'country' => $data['country'],
            'business_name' => $data['business_name'],
            'business_address' => $data['business_address'],
            'nature_of_business' => $data['nature_of_business'],
            'sub_sector' => $data['sub_sector'],
            'identity_type' => $data['identity_type'],
            'id_number' => $data['id_number'],
            'date_of_issue' => $data['date_of_issue'],
            'registration_status' => $data['registration_status'],
            'chapter' => $data['chapter'],
            'zone' => $data['zone'],
            'member_type' => $data['member_type'],
            'payment_type' => $data['payment_type'],
            'membership_number' => $membershipNumber,
            'photo' => $photo,
            'password' => $hashedPassword,
            'payment_status' => $data['payment_type'] === 'Online Payment' ? 'Pending' : 'Paid',
            'account_name' => $data['account_name'],
            'account_number' => $data['account_number'],
            'bank_name' => $data['bank_name'],
            'nin_card' => $nin_card,
            'signature' => $signature
        ]);

        if ($ok) {
            return ['success' => true, 'membership_number' => $membershipNumber];
        }
        return ['success' => false, 'message' => 'Failed to add member.'];
    }

    public function editMember($id, $data, $file = null)
    {
        // Unique email check (exclude current member)
        $stmt = $this->getConnection()->prepare("SELECT id FROM members WHERE email = :email AND id != :id");
        $stmt->execute(['email' => $data['email'], 'id' => $id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already exists.'];
        }

        // Unique phone check (exclude current member)
        $phone = $data['phone_country_code'] . $data['contact_number'];
        $stmt = $this->getConnection()->prepare("SELECT id FROM members WHERE contact_number = :phone AND id != :id");
        $stmt->execute(['phone' => $phone, 'id' => $id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Phone number already exists.'];
        }

        $member = $this->getMember($id);

        // Handle file uploads
        $photo = $member['photo'] ?? 'default.jpg';
        if ($file && isset($file['tmp_name']) && $file['tmp_name']) {
            $photo = $this->handlePhotoUpload($file);
        }

        $nin_card = $member['nin_card'] ?? 'default_nin.jpg';
        if ($_FILES['nin_card'] && isset($_FILES['nin_card']['tmp_name']) && $_FILES['nin_card']['tmp_name']) {
            $nin_card = $this->handleFileUpload($_FILES['nin_card'], 'nin_cards', 'default_nin.jpg');
        }

        $signature = $member['signature'] ?? 'default_signature.jpg';
        if ($_FILES['signature'] && isset($_FILES['signature']['tmp_name']) && $_FILES['signature']['tmp_name']) {
            $signature = $this->handleFileUpload($_FILES['signature'], 'signatures', 'default_signature.jpg');
        }

        // Password handling
        $passwordUpdate = '';
        $passwordParams = [];
        if (!empty($data['password'])) {
            $passwordUpdate = ', password = :password';
            $passwordParams['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql = "UPDATE members SET 
            title = :title, surname = :surname, firstname = :firstname, othername = :othername, 
            gender = :gender, marital_status = :marital_status, email = :email, contact_number = :contact_number, 
            whatsapp_number = :whatsapp_number, dob = :dob, house_no = :house_no, street_name = :street_name, 
            nearest_bus_stop = :nearest_bus_stop, city_town = :city_town, lga = :lga, state_district = :state_district, 
            country = :country, business_name = :business_name, business_address = :business_address, 
            nature_of_business = :nature_of_business, sub_sector = :sub_sector, identity_type = :identity_type, 
            id_number = :id_number, date_of_issue = :date_of_issue, registration_status = :registration_status, 
            chapter = :chapter, zone = :zone, photo = :photo, nin_card = :nin_card, signature = :signature,
            payment_status = :payment_status, annual_dues_status = :annual_dues_status, 
            account_name = :account_name, account_number = :account_number, bank_name = :bank_name" .
            $passwordUpdate . " WHERE id = :id";

        $params = [
            'title' => $data['title'],
            'surname' => $data['surname'],
            'firstname' => $data['firstname'],
            'othername' => $data['othername'],
            'gender' => $data['gender'],
            'marital_status' => $data['marital_status'],
            'email' => $data['email'],
            'contact_number' => $phone,
            'whatsapp_number' => $data['whatsapp_country_code'] . $data['whatsapp_number'],
            'dob' => $data['dob'],
            'house_no' => $data['house_no'],
            'street_name' => $data['street_name'],
            'nearest_bus_stop' => $data['nearest_bus_stop'],
            'city_town' => $data['city_town'],
            'lga' => $data['lga'],
            'state_district' => $data['state_district'],
            'country' => $data['country'],
            'business_name' => $data['business_name'],
            'business_address' => $data['business_address'],
            'nature_of_business' => $data['nature_of_business'],
            'sub_sector' => $data['sub_sector'],
            'identity_type' => $data['identity_type'],
            'id_number' => $data['id_number'],
            'date_of_issue' => $data['date_of_issue'],
            'registration_status' => $data['registration_status'],
            'chapter' => $data['chapter'],
            'zone' => $data['zone'],
            'photo' => $photo,
            'nin_card' => $nin_card,
            'signature' => $signature,
            'payment_status' => $data['payment_status'],
            'annual_dues_status' => $data['annual_dues_status'],
            'account_name' => $data['account_name'],
            'account_number' => $data['account_number'],
            'bank_name' => $data['bank_name'],
            'id' => $id
        ];

        // Add password parameter if provided
        if (!empty($data['password'])) {
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $stmt = $this->getConnection()->prepare($sql);
        $ok = $stmt->execute($params);

        return $ok ? ['success' => true] : ['success' => false, 'message' => 'Failed to update member.'];
    }

    public function deleteMember($id)
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM members WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function register($data, $file = null)
    {
        return $this->addMember($data, $file);
    }

    private function handlePhotoUpload($file)
    {
        $allowedExts = ['jpg', 'jpeg', 'png'];
        $allowedMimes = ['image/jpeg', 'image/png'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        $dest = __DIR__ . '/../../../public/uploads/member_photos';
        
        $res = \App\Helpers\SecurityHelper::handleSecureUpload($file, $allowedExts, $allowedMimes, $maxSize, $dest, 'photo_');
        return is_array($res) ? 'default.jpg' : $res;
    }

    private function handleFileUpload($file, $subDir, $defaultFile)
    {
        $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
        $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $dest = __DIR__ . '/../../../public/uploads/' . $subDir;

        $res = \App\Helpers\SecurityHelper::handleSecureUpload($file, $allowedExts, $allowedMimes, $maxSize, $dest, 'file_');
        return is_array($res) ? $defaultFile : $res;
    }

    public function getAllUsers()
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalMembers()
    {
        $stmt = $this->getConnection()->prepare("SELECT COUNT(*) as count FROM members");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    public function getActiveMembersCount()
    {
        // Consider members active if they have activity in the last 90 days
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(DISTINCT m.id) as count 
            FROM members m 
            LEFT JOIN annual_dues ad ON m.id = ad.member_id 
            LEFT JOIN shares s ON m.id = s.member_id 
            WHERE ad.payment_date >= DATE_SUB(NOW(), INTERVAL 90 DAY) 
               OR s.purchase_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)
               OR m.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    public function getExpiringMembersCount($expiringDate)
    {
        // This is a simplified version - you might want to track membership expiration dates
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*) as count 
            FROM members 
            WHERE DATE_ADD(created_at, INTERVAL 1 YEAR) <= :expiring_date
        ");
        $stmt->execute(['expiring_date' => $expiringDate]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    public function getLowBalanceMembersCount()
    {
        // This is a placeholder - implement based on your business logic
        return 0;
    }

    public function getRecentMembers($limit = 5)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT id, firstname, surname, created_at 
            FROM members 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getNewMembersThisMonth()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*) as count 
            FROM members 
            WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    public function getRenewalRate()
    {
        // This is a simplified calculation - you might want to implement based on your business logic
        $stmt = $this->getConnection()->prepare("
            SELECT 
                COUNT(*) as total_members,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR) THEN 1 END) as renewed_members
            FROM members
        ");
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result['total_members'] > 0) {
            return round(($result['renewed_members'] / $result['total_members']) * 100, 1);
        }

        return 0;
    }

    public function getMembersInDateRange($dateFrom, $dateTo)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT id, firstname, surname, email, created_at, status
            FROM members 
            WHERE created_at BETWEEN :date_from AND :date_to
            ORDER BY created_at DESC
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        return $stmt->fetchAll();
    }

    public function getMemberCountByType()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT mt.type, COUNT(m.id) as count
            FROM members m
            JOIN membership_types mt ON m.membership_type = mt.id
            GROUP BY mt.type
            ORDER BY count DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getUpcomingRenewals($days)
    {
        // This is a simplified example assuming annual renewals based on join date.
        // A more robust implementation would have a dedicated `next_renewal_date` column.
        $stmt = $this->getConnection()->prepare("
            SELECT id, firstname, surname, email, created_at,
                   DATE_ADD(created_at, INTERVAL 1 YEAR) as renewal_date
            FROM members
            WHERE DATE_ADD(created_at, INTERVAL 1 YEAR) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY renewal_date ASC
        ");
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopMembersByDues($limit = 5)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT m.id, m.firstname, m.surname, m.email, SUM(ad.amount) as total_dues
            FROM members m
            JOIN annual_dues ad ON m.id = ad.member_id
            GROUP BY m.id
            ORDER BY total_dues DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopMembersByShares($limit = 5)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT m.id, m.firstname, m.surname, m.email, SUM(s.amount) as total_shares
            FROM members m
            JOIN shares s ON m.id = s.member_id
            GROUP BY m.id
            ORDER BY total_shares DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMemberGeoDistribution()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT country, COUNT(*) as count
            FROM members
            WHERE country IS NOT NULL AND country != ''
            GROUP BY country
            ORDER BY count DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPaginatedMembers($page = 1, $perPage = 10, $search = '', $status = '', $memberType = '')
    {
        $offset = ($page - 1) * $perPage;

        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(firstname LIKE :search_firstname OR surname LIKE :search_surname OR email LIKE :search_email OR membership_number LIKE :search_membership OR contact_number LIKE :search_contact)";
            $params[':search_firstname'] = '%' . $search . '%';
            $params[':search_surname'] = '%' . $search . '%';
            $params[':search_email'] = '%' . $search . '%';
            $params[':search_membership'] = '%' . $search . '%';
            $params[':search_contact'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $whereConditions[] = "payment_status = :status";
            $params[':status'] = $status;
        }

        if (!empty($memberType)) {
            $whereConditions[] = "member_type = :member_type";
            $params[':member_type'] = $memberType;
        }

        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        }

        $sql = "SELECT * FROM members $whereClause ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalMemberCount($search = '', $status = '', $memberType = '')
    {
        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(firstname LIKE :search_firstname OR surname LIKE :search_surname OR email LIKE :search_email OR membership_number LIKE :search_membership OR contact_number LIKE :search_contact)";
            $params[':search_firstname'] = '%' . $search . '%';
            $params[':search_surname'] = '%' . $search . '%';
            $params[':search_email'] = '%' . $search . '%';
            $params[':search_membership'] = '%' . $search . '%';
            $params[':search_contact'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $whereConditions[] = "payment_status = :status";
            $params[':status'] = $status;
        }

        if (!empty($memberType)) {
            $whereConditions[] = "member_type = :member_type";
            $params[':member_type'] = $memberType;
        }

        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        }

        $sql = "SELECT COUNT(*) FROM members $whereClause";
        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getMemberById($id)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM members WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getAllMembersFiltered($search = '', $status = '', $memberType = '')
    {
        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(firstname LIKE :search_firstname OR surname LIKE :search_surname OR email LIKE :search_email OR membership_number LIKE :search_membership OR contact_number LIKE :search_contact)";
            $params[':search_firstname'] = '%' . $search . '%';
            $params[':search_surname'] = '%' . $search . '%';
            $params[':search_email'] = '%' . $search . '%';
            $params[':search_membership'] = '%' . $search . '%';
            $params[':search_contact'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $whereConditions[] = "payment_status = :status";
            $params[':status'] = $status;
        }

        if (!empty($memberType)) {
            $whereConditions[] = "member_type = :member_type";
            $params[':member_type'] = $memberType;
        }

        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        }

        $sql = "SELECT * FROM members $whereClause ORDER BY id DESC";
        $stmt = $this->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMemberByEmail($email)
    {
        $stmt = $this->getConnection()->prepare('SELECT * FROM members WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function updatePasswordByEmail($email, $password)
    {
        $stmt = $this->getConnection()->prepare('UPDATE members SET password = :password WHERE email = :email');
        return $stmt->execute([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }
    public function verifyEmail($code)
    {
        $stmt = $this->getConnection()->prepare("SELECT id FROM members WHERE reset_token = :code AND reset_token_expiry > NOW()");
        $stmt->execute(['code' => $code]);
        $member = $stmt->fetch();

        if ($member) {
            $stmt = $this->getConnection()->prepare("UPDATE members SET reset_token = NULL, reset_token_expiry = NULL, payment_status = IF(payment_status = 'Pending', 'Paid', payment_status) WHERE id = :id");
            $stmt->execute(['id' => $member['id']]);
            return true;
        }
        return false;
    }

    public function sendPasswordReset($email)
    {
        $member = $this->getMemberByEmail($email);
        if (!$member) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->getConnection()->prepare("UPDATE members SET reset_token = :token, reset_token_expiry = :expiry WHERE id = :id");
        if ($stmt->execute(['token' => $token, 'expiry' => $expiry, 'id' => $member['id']])) {
            // In a real application, you would send an email here.
            // For example:
            // $link = "https://example.com/reset-password?token=$token";
            // mail($email, "Password Reset", "Click here: $link");
            return true;
        }
        return false;
    }

    public function resetPassword($token, $password)
    {
        $stmt = $this->getConnection()->prepare("SELECT id FROM members WHERE reset_token = :token AND reset_token_expiry > NOW()");
        $stmt->execute(['token' => $token]);
        $member = $stmt->fetch();

        if (!$member) {
            return false;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->getConnection()->prepare("UPDATE members SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id");
        return $stmt->execute(['password' => $hashedPassword, 'id' => $member['id']]);
    }

    /**
     * Generates a unique membership number
     * Ensures no duplicates by checking against the database
     */
    private function generateUniqueMembershipNumber()
    {
        // 1. Find the highest existing number based on string sorting (length first, then value)
        // This handles cases where numbers might be GAFCONL-9 and GAFCONL-10
        $stmt = $this->getConnection()->prepare("
            SELECT membership_number 
            FROM members 
            WHERE membership_number LIKE 'GAFCONL-%' 
            ORDER BY LENGTH(membership_number) DESC, membership_number DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $result = $stmt->fetch();

        $lastSequence = 0;
        if ($result && preg_match('/GAFCONL-(\d+)/', $result['membership_number'], $matches)) {
            $lastSequence = (int) $matches[1];
        }

        // 2. Loop to find the next available number
        // This handles gaps and race conditions where the 'last' number might actually be taken or skipped
        $nextSequence = $lastSequence;
        do {
            $nextSequence++;
            $candidate = 'GAFCONL-' . str_pad($nextSequence, 7, '0', STR_PAD_LEFT);

            // Check if this specific candidate already exists
            $check = $this->getConnection()->prepare("SELECT id FROM members WHERE membership_number = ?");
            $check->execute([$candidate]);
        } while ($check->fetch());

        return $candidate;
    }
}