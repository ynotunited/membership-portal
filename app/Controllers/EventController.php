<?php

namespace App\Controllers;

use App\Models\EventModel;

class EventController extends BaseController
{
    private $eventModel;

    public function __construct()
    {
        $this->requireAdmin(); // Protect ALL event management endpoints
        $this->eventModel = new EventModel();
    }

    public function index()
    {
        $events = $this->eventModel->getAllEvents();
        $title = 'Manage Events';
        $data = [
            'events' => $events,
            'title' => $title,
            'pageTitle' => $title
        ];
        $this->render('admin/events', $data);
    }

    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'start_date' => $_POST['start_date'] ?? '',
                'end_date' => $_POST['end_date'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];

            if (empty($data['title']) || empty($data['description']) || empty($data['start_date']) || empty($data['end_date'])) {
                $this->setFlashMessage('error', 'All fields are required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/events/add');
                exit;
            }

            $result = $this->eventModel->addEvent($data);
            if ($result) {
                $this->setFlashMessage('success', 'Event added successfully!');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/events');
                exit;
            } else {
                $this->setFlashMessage('error', 'Failed to add event.');
            }
        }

        $title = 'Add Event';
        $data = [
            'title' => $title,
            'pageTitle' => $title
        ];
        $this->render('admin/events_add', $data);
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/events');
            exit;
        }

        $event = $this->eventModel->getEvent($id);
        if (!$event) {
            $this->setFlashMessage('error', 'Event not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/events');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'start_date' => $_POST['start_date'] ?? '',
                'end_date' => $_POST['end_date'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];

            if (empty($data['title']) || empty($data['description']) || empty($data['start_date']) || empty($data['end_date'])) {
                $this->setFlashMessage('error', 'All fields are required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/events/edit?id=' . $id);
                exit;
            }

            $result = $this->eventModel->updateEvent($id, $data);
            if ($result) {
                $this->setFlashMessage('success', 'Event updated successfully!');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/events');
                exit;
            } else {
                $this->setFlashMessage('error', 'Failed to update event.');
            }
        }

        $title = 'Edit Event';
        $data = [
            'event' => $event,
            'title' => $title,
            'pageTitle' => $title
        ];
        $this->render('admin/events_edit', $data);
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/events');
            exit;
        }

        $result = $this->eventModel->deleteEvent($id);
        if ($result) {
            $this->setFlashMessage('success', 'Event deleted successfully!');
        } else {
            $this->setFlashMessage('error', 'Failed to delete event.');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/events');
        exit;
    }
} 