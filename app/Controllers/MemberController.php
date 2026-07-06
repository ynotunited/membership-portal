<?php
namespace App\Controllers;

// Include the custom autoloader for PhpSpreadsheet
if (file_exists(__DIR__ . '/../../autoload_phpspreadsheet.php')) {
    require_once __DIR__ . '/../../autoload_phpspreadsheet.php';
}
// Include the custom autoloader for PSR classes
if (file_exists(__DIR__ . '/../../autoload_psr.php')) {
    require_once __DIR__ . '/../../autoload_psr.php';
}

use App\Controllers\BaseController;
use App\Models\MemberModel;
use App\Helpers\Url;
use App\Helpers\PermissionHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use TCPDF;

class MemberController extends BaseController
{
    private $memberModel;
    public function __construct()
    {
        $this->requireAnyPermission(['members.view', 'members.create', 'members.edit', 'members.delete']);
        $this->memberModel = new MemberModel();
    }

    public function index()
    {
        $this->requirePermission('members.view');
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $memberType = trim($_GET['member_type'] ?? '');
        
        // Validate per_page to prevent abuse
        $allowedPerPage = [10, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $model = new MemberModel();
        $members = $model->getPaginatedMembers($page, $perPage, $search, $status, $memberType);
        $totalMembers = $model->getTotalMemberCount($search, $status, $memberType);
        $totalPages = ceil($totalMembers / $perPage);
        
        $this->render('admin/members/index', [
            'members' => $members,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pageTitle' => 'Manage Members',
            'search' => $search,
            'status' => $status,
            'memberType' => $memberType,
            'perPage' => $perPage
        ]);
    }

    public function add()
    {
        $this->requirePermission('members.create');
        
        $error = $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'surname' => trim($_POST['surname'] ?? ''),
                'firstname' => trim($_POST['firstname'] ?? ''),
                'othername' => trim($_POST['othername'] ?? ''),
                'gender' => trim($_POST['gender'] ?? ''),
                'marital_status' => trim($_POST['marital_status'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone_country_code' => trim($_POST['phone_country_code'] ?? '+234'),
                'contact_number' => trim($_POST['contact_number'] ?? ''),
                'whatsapp_country_code' => trim($_POST['whatsapp_country_code'] ?? '+234'),
                'whatsapp_number' => trim($_POST['whatsapp_number'] ?? ''),
                'dob' => trim($_POST['dob'] ?? ''),
                'house_no' => trim($_POST['house_no'] ?? ''),
                'street_name' => trim($_POST['street_name'] ?? ''),
                'nearest_bus_stop' => trim($_POST['nearest_bus_stop'] ?? ''),
                'city_town' => trim($_POST['city_town'] ?? ''),
                'lga' => trim($_POST['lga'] ?? ''),
                'state_district' => trim($_POST['state_district'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'business_name' => trim($_POST['business_name'] ?? ''),
                'business_address' => trim($_POST['business_address'] ?? ''),
                'nature_of_business' => trim($_POST['nature_of_business'] ?? ''),
                'sub_sector' => trim($_POST['sub_sector'] ?? ''),
                'identity_type' => trim($_POST['identity_type'] ?? ''),
                'id_number' => trim($_POST['id_number'] ?? ''),
                'date_of_issue' => trim($_POST['date_of_issue'] ?? ''),
                'registration_status' => trim($_POST['registration_status'] ?? ''),
                'chapter' => trim($_POST['chapter'] ?? ''),
                'zone' => trim($_POST['zone'] ?? ''),
                'member_type' => trim($_POST['member_type'] ?? ''),
                'payment_type' => trim($_POST['payment_type'] ?? 'Online Payment'),
                'account_name' => trim($_POST['account_name'] ?? ''),
                'account_number' => trim($_POST['account_number'] ?? ''),
                'bank_name' => trim($_POST['bank_name'] ?? ''),
                'password' => $_POST['password'] ?? ''
            ];
            
            $file = $_FILES['photo'] ?? null;
            $nin_card = $_FILES['nin_card'] ?? null;
            $signature = $_FILES['signature'] ?? null;
            
            if (!$data['title'] || !$data['surname'] || !$data['firstname'] || !$data['gender'] || !$data['marital_status'] || 
                !$data['email'] || !$data['contact_number'] || !$data['dob'] || !$data['house_no'] || !$data['street_name'] || 
                !$data['nearest_bus_stop'] || !$data['city_town'] || !$data['lga'] || !$data['state_district'] || 
                !$data['country'] || !$data['identity_type'] || !$data['id_number'] || 
                !$data['date_of_issue'] || !$data['registration_status'] || !$data['chapter'] || !$data['member_type'] || !$data['password']) {
                $error = 'All required fields must be filled.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email address.';
            } elseif (strlen($data['password']) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } elseif ($data['identity_type'] === 'NIN' && !preg_match('/^\d{11}$/', $data['id_number'])) {
                $error = 'Invalid NIN format. NIN should be exactly 11 digits.';
            } else {
                $model = new MemberModel();
                $result = $model->addMember($data, $file);
                if ($result['success']) {
                    $success = 'Member added successfully! Membership Number: ' . $result['membership_number'];
                } else {
                    $error = $result['message'] ?? 'Failed to add member.';
                }
            }
        }
        $this->render('admin/members/add', ['error' => $error, 'success' => $success]);
    }

    public function edit()
    {
        $this->requirePermission('members.edit');
        
        $error = $success = '';
        $id = $_GET['id'] ?? null;
        $model = new MemberModel();
        $member = $id ? $model->getMember($id) : null;
        // Fetch dues and shares history for the view
        $annualDuesModel = new \App\Models\AnnualDuesModel();
        $sharesModel = new \App\Models\SharesModel();
        $annualDuesHistory = $id ? $annualDuesModel->getAnnualDuesByMember($id) : [];
        $sharesHistory = $id ? $sharesModel->getSharesByMember($id) : [];
        $totalShares = $id ? $sharesModel->getTotalSharesByMember($id) : 0;
        if (!$member) {
            $error = 'Member not found.';
            $this->render('members/edit', ['error' => $error, 'success' => $success, 'member' => null, 'annualDuesHistory' => [], 'sharesHistory' => [], 'totalShares' => 0]);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'surname' => trim($_POST['surname'] ?? ''),
                'firstname' => trim($_POST['firstname'] ?? ''),
                'othername' => trim($_POST['othername'] ?? ''),
                'gender' => trim($_POST['gender'] ?? ''),
                'marital_status' => trim($_POST['marital_status'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone_country_code' => trim($_POST['phone_country_code'] ?? '+234'),
                'contact_number' => trim($_POST['contact_number'] ?? ''),
                'whatsapp_country_code' => trim($_POST['whatsapp_country_code'] ?? '+234'),
                'whatsapp_number' => trim($_POST['whatsapp_number'] ?? ''),
                'dob' => trim($_POST['dob'] ?? ''),
                'house_no' => trim($_POST['house_no'] ?? ''),
                'street_name' => trim($_POST['street_name'] ?? ''),
                'nearest_bus_stop' => trim($_POST['nearest_bus_stop'] ?? ''),
                'city_town' => trim($_POST['city_town'] ?? ''),
                'lga' => trim($_POST['lga'] ?? ''),
                'state_district' => trim($_POST['state_district'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'business_name' => trim($_POST['business_name'] ?? ''),
                'business_address' => trim($_POST['business_address'] ?? ''),
                'nature_of_business' => trim($_POST['nature_of_business'] ?? ''),
                'sub_sector' => trim($_POST['sub_sector'] ?? ''),
                'identity_type' => trim($_POST['identity_type'] ?? ''),
                'id_number' => trim($_POST['id_number'] ?? ''),
                'date_of_issue' => trim($_POST['date_of_issue'] ?? ''),
                'registration_status' => trim($_POST['registration_status'] ?? ''),
                'chapter' => trim($_POST['chapter'] ?? ''),
                'zone' => trim($_POST['zone'] ?? ''),
                'account_name' => trim($_POST['account_name'] ?? ''),
                'account_number' => trim($_POST['account_number'] ?? ''),
                'bank_name' => trim($_POST['bank_name'] ?? ''),
                'payment_status' => trim($_POST['payment_status'] ?? ''),
                'annual_dues_status' => trim($_POST['annual_dues_status'] ?? ''),
                'password' => $_POST['password'] ?? ''
            ];
            
            $file = $_FILES['photo'] ?? null;
            $nin_card = $_FILES['nin_card'] ?? null;
            $signature = $_FILES['signature'] ?? null;
            
            if (!$data['title'] || !$data['surname'] || !$data['firstname'] || !$data['gender'] || !$data['marital_status'] || 
                !$data['email'] || !$data['contact_number'] || !$data['dob'] || !$data['house_no'] || !$data['street_name'] || 
                !$data['nearest_bus_stop'] || !$data['city_town'] || !$data['lga'] || !$data['state_district'] || 
                !$data['country'] || !$data['identity_type'] || !$data['id_number'] || 
                !$data['date_of_issue'] || !$data['registration_status'] || !$data['chapter']) {
                $error = 'All required fields must be filled.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email address.';
            } elseif ($data['password'] && strlen($data['password']) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } elseif ($data['identity_type'] === 'NIN' && !preg_match('/^\d{11}$/', $data['id_number'])) {
                $error = 'Invalid NIN format. NIN should be exactly 11 digits.';
            } else {
                $result = $model->editMember($id, $data, $file);
                if ($result['success']) {
                    $success = 'Member updated successfully!';
                    $member = $model->getMember($id);
                } else {
                    $error = $result['message'] ?? 'Failed to update member.';
                }
            }
            // Handle manual annual dues addition
            if (!empty($_POST['manual_annual_dues_amount']) && !empty($_POST['manual_annual_dues_status']) && !empty($_POST['manual_annual_dues_date'])) {
                $annualDuesModel->addAnnualDues($id, $_POST['manual_annual_dues_amount'], $_POST['manual_annual_dues_status'], $_POST['manual_annual_dues_date'], $_POST['manual_annual_dues_notes'] ?? null);
                $success .= ' Annual dues added.';
            }
            // Handle manual shares addition
            if (!empty($_POST['manual_shares']) && !empty($_POST['manual_share_amount']) && !empty($_POST['manual_share_date'])) {
                $sharesModel->addShares($id, $_POST['manual_shares'], $_POST['manual_share_amount'], $_POST['manual_share_date'], $_POST['manual_share_notes'] ?? null);
                $success .= ' Shares added.';
            }
            // Refresh histories
            $annualDuesHistory = $annualDuesModel->getAnnualDuesByMember($id);
            $sharesHistory = $sharesModel->getSharesByMember($id);
            $totalShares = $sharesModel->getTotalSharesByMember($id);
        }
        $this->render('members/edit', [
            'error' => $error,
            'success' => $success,
            'member' => $member,
            'annualDuesHistory' => $annualDuesHistory,
            'sharesHistory' => $sharesHistory,
            'totalShares' => $totalShares
        ]);
    }

    public function delete()
    {
        $this->requirePermission('members.delete');
        
        $error = $success = '';
        $id = $_GET['id'] ?? null;
        $model = new MemberModel();
        if (!$id || !$model->getMember($id)) {
            $error = 'Member not found.';
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($model->deleteMember($id)) {
                    $success = 'Member deleted successfully!';
                } else {
                    $error = 'Failed to delete member.';
                }
            }
        }
        $this->render('members/delete', ['error' => $error, 'success' => $success, 'id' => $id]);
    }

    public function profile()
    {
        $this->requirePermission('members.view');

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->setFlashMessage('error', 'Invalid member ID.');
            header('Location: ' . Url::appUrl() . '/members');
            exit;
        }

        $model = new MemberModel();
        $member = $model->getMember($id);
        if (!$member) {
            $this->setFlashMessage('error', 'Member not found.');
            header('Location: ' . Url::appUrl() . '/members');
            exit;
        }

        $this->render('members/profile', ['member' => $member]);
    }

    public function membershipCard()
    {
        $this->requirePermission('members.view');

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->setFlashMessage('error', 'Invalid member ID.');
            header('Location: ' . Url::appUrl() . '/members');
            exit;
        }

        $model = new MemberModel();
        $member = $model->getMember($id);
        if (!$member) {
            $this->setFlashMessage('error', 'Member not found.');
            header('Location: ' . Url::appUrl() . '/members');
            exit;
        }

        $this->render('members/membership-card', ['member' => $member]);
    }

    public function export()
    {
        $this->requirePermission('members.view');

        // Rate-limit exports: 5 per 5 min per admin user (prevents bulk data scraping)
        [$max, $win] = \App\Helpers\RateLimiter::limitsFor('member_export');
        \App\Helpers\RateLimiter::enforceForHtml(
            'member_export',
            'user_' . ($_SESSION['user_id'] ?? \App\Helpers\RateLimiter::clientIp()),
            $max, $win,
            \App\Helpers\Url::appUrl() . '/members'
        );
        $ids = $_GET['ids'] ?? [];
        $format = $_GET['format'] ?? 'csv';
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $memberType = trim($_GET['member_type'] ?? '');
        
        $model = new MemberModel();
        
        // Get members data
        if (!empty($ids) && is_array($ids)) {
            // Export selected members
            $members = $model->getMembersByIds(array_map('intval', $ids));
        } else {
            // Export all filtered members
            $members = $model->getAllMembersFiltered($search, $status, $memberType);
        }
        
        // Define column headers
        $headers = [
            'ID', 'Membership Number', 'Title', 'Surname', 'First Name', 'Other Name', 'Gender', 
            'Marital Status', 'Email', 'Phone Country Code', 'Contact Number', 'WhatsApp Country Code', 
            'WhatsApp Number', 'Date of Birth', 'House No', 'Street Name', 'Nearest Bus Stop', 
            'City/Town', 'LGA', 'State/District', 'Country', 'Business Name', 'Business Address', 
            'Nature of Business', 'Sub Sector', 'Identity Type', 'ID Number', 'Date of Issue', 
            'Registration Status', 'Chapter', 'Zone', 'Member Type', 'Payment Type', 'Payment Status', 
            'Account Name', 'Account Number', 'Bank Name', 'Created At'
        ];
        
        // Prepare data
        $data = [];
        foreach ($members as $member) {
            $data[] = [
                $member['id'],
                $member['membership_number'],
                $member['title'],
                $member['surname'],
                $member['firstname'],
                $member['othername'],
                $member['gender'],
                $member['marital_status'],
                $member['email'],
                $member['phone_country_code'],
                $member['contact_number'],
                $member['whatsapp_country_code'],
                $member['whatsapp_number'],
                $member['dob'],
                $member['house_no'],
                $member['street_name'],
                $member['nearest_bus_stop'],
                $member['city_town'],
                $member['lga'],
                $member['state_district'],
                $member['country'],
                $member['business_name'],
                $member['business_address'],
                $member['nature_of_business'],
                $member['sub_sector'],
                $member['identity_type'],
                $member['id_number'],
                $member['date_of_issue'],
                $member['registration_status'],
                $member['chapter'],
                $member['zone'],
                $member['member_type'],
                $member['payment_type'],
                $member['payment_status'],
                $member['account_name'],
                $member['account_number'],
                $member['bank_name'],
                $member['created_at']
            ];
        }
        
        // Export based on format
        switch (strtolower($format)) {
            case 'xlsx':
                $this->exportXlsx($headers, $data);
                break;
            case 'pdf':
                $this->exportPdf($headers, $data);
                break;
            case 'csv':
            default:
                $this->exportCsv($headers, $data);
                break;
        }
    }
    
    private function exportCsv($headers, $data)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="members_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    private function exportXlsx($headers, $data)
    {
        // Check if PhpSpreadsheet is installed
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            // Fallback to CSV if PhpSpreadsheet is not available
            $this->exportCsv($headers, $data);
            return;
        }
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }
        
        // Set data
        $row = 2;
        foreach ($data as $rowData) {
            $column = 'A';
            foreach ($rowData as $cellData) {
                $sheet->setCellValue($column . $row, $cellData);
                $column++;
            }
            $row++;
        }
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="members_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
    
    private function exportPdf($headers, $data)
    {
        // Check if TCPDF is available
        if (!class_exists('\TCPDF')) {
            // Fallback to CSV if TCPDF is not available
            $this->exportCsv($headers, $data);
            return;
        }
        
        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('Members Export');
        
        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // Set font
        $pdf->SetFont('helvetica', '', 8);
        
        // Add a page
        $pdf->AddPage();
        
        // Create HTML table
        $html = '<h1>Members Export</h1>';
        $html .= '<table border="1" cellpadding="4">';
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead>';
        $html .= '<tbody>';
        
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        
        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        
        // Close and output PDF document
        $pdf->Output('members_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }

    public function bulk()
    {
        $this->requirePermission('members.edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Url::appUrl() . '/members');
            exit;
        }
        $action = trim($_POST['action'] ?? '');
        $ids = $_POST['selected'] ?? [];
        if (!$action || empty($ids) || !is_array($ids)) {
            header('Location: ' . Url::appUrl() . '/members');
            exit;
        }

        $ids = array_map('intval', $ids);
        $model = new MemberModel();
        $db = $model->getConnection();

        switch ($action) {
            case 'approve':
                $stmt = $db->prepare('UPDATE members SET payment_status = "Paid" WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
                $stmt->execute($ids);
                break;
            case 'delete':
                // Soft delete could be safer; here we do hard delete minimal
                $stmt = $db->prepare('DELETE FROM members WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
                $stmt->execute($ids);
                break;
            case 'email':
                $subject = trim($_POST['email_subject'] ?? 'Message from 24/7 Registration Portal');
                $message = trim($_POST['email_message'] ?? '');
                if ($message !== '') {
                    $members = $model->getMembersByIds($ids);
                    $recipients = [];
                    foreach ($members as $m) {
                        if (!empty($m['email'])) {
                            $name = trim(($m['firstname'] ?? '') . ' ' . ($m['surname'] ?? ''));
                            $recipients[] = ['email' => $m['email'], 'name' => ($name !== '' ? $name : $m['email'])];
                        }
                    }
                    if (!empty($recipients)) {
                        try {
                            $emailService = new \App\Services\EmailNotificationService();
                            $emailService->sendBulkEmail($recipients, $subject, nl2br($message));
                        } catch (\Exception $e) {
                            error_log('Bulk email error: ' . $e->getMessage());
                        }
                    }
                }
                break;
            case 'export_csv':
            case 'export_pdf':
            case 'export_xlsx':
                $format = $action === 'export_pdf' ? 'pdf' : ($action === 'export_xlsx' ? 'xlsx' : 'csv');
                $query = http_build_query(['ids' => $ids, 'format' => $format]);
                header('Location: ' . Url::appUrl() . '/members/export?' . $query);
                exit;
        }

        header('Location: ' . Url::appUrl() . '/members');
        exit;
    }
}