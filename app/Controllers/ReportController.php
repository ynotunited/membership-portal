<?php

namespace App\Controllers;

use App\Models\MemberModel;

class ReportController extends BaseController
{
    private $memberModel;

    public function __construct()
    {
        // parent::__construct();
        $this->memberModel = new MemberModel();
    }

    public function index()
    {
        $members = [];
        $fromDate = '';
        $toDate = '';
        $reportGenerated = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fromDate = $_POST['fromDate'] ?? '';
            $toDate = $_POST['toDate'] ?? '';
            
            if (!empty($fromDate) && !empty($toDate)) {
                $members = $this->memberModel->getMembersByDateRange($fromDate, $toDate);
                $reportGenerated = true;
            }
        }

        $title = 'Membership Report';
        extract([
            'members' => $members, 
            'fromDate' => $fromDate, 
            'toDate' => $toDate, 
            'reportGenerated' => $reportGenerated,
            'title' => $title
        ]);
        require dirname(__DIR__) . '/Views/reports/index.php';
    }
} 