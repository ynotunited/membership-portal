<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MembershipTypeModel;

class MembershipTypeController extends BaseController
{
    private $typeModel;

    public function __construct()
    {
        $this->requireAdmin();
        $this->typeModel = new MembershipTypeModel();
    }

    public function index()
    {
        $types = $this->typeModel->getAll();
        $this->render('admin/membership_types/index', [
            'types' => $types,
            'pageTitle' => 'Membership Types'
        ]);
    }

    public function add()
    {
        $error = $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = trim($_POST['type'] ?? '');
            $amount = trim($_POST['amount'] ?? '');
            if (!$type || !$amount) {
                $error = 'All fields are required.';
            } else {
                $ok = $this->typeModel->add($type, $amount);
                if ($ok) {
                    $success = 'Membership type added successfully!';
                } else {
                    $error = 'Failed to add membership type.';
                }
            }
        }
        $this->render('admin/membership_types/add', [
            'error' => $error,
            'success' => $success,
            'pageTitle' => 'Add Membership Type'
        ]);
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        $error = $success = '';
        if (!$id) {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/membership-types');
            exit;
        }
        $type = $this->typeModel->getById($id);
        if (!$type) {
            $error = 'Membership type not found.';
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newType = trim($_POST['type'] ?? '');
            $amount = trim($_POST['amount'] ?? '');
            if (!$newType || !$amount) {
                $error = 'All fields are required.';
            } else {
                $ok = $this->typeModel->update($id, $newType, $amount);
                if ($ok) {
                    $success = 'Membership type updated successfully!';
                    $type = $this->typeModel->getById($id);
                } else {
                    $error = 'Failed to update membership type.';
                }
            }
        }
        $this->render('admin/membership_types/edit', [
            'type' => $type,
            'error' => $error,
            'success' => $success,
            'pageTitle' => 'Edit Membership Type'
        ]);
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->typeModel->delete($id);
        }
        header('Location: ' . \App\Helpers\Url::appUrl() . '/membership-types');
        exit;
    }
} 