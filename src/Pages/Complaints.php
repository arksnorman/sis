<?php

namespace App\Pages;


use App\Models\Complaint;
use App\Controller\AppController;
use Symfony\Component\HttpFoundation\Response;

class Complaints extends AppController
{
    private $post;
    private $complaintStorage;

    public function __construct()
    {
        $this->post = $this->getPost();
        $this->complaintStorage = $this->getStorageManager()->getComplaintStorage();
    }

    public function index() :Response
    {
        if (!$this->getAuthenticator()->isLoggedIn())
            return $this->redirectToRoute('index');

        $this->getAuthenticator()->requireAdmin();

        return $this->renderTemplate('complaints/index.html.twig', [
            'success' => $this->getSession()->flash('deleteSuccess'),
            'pageTitle' => 'All Complaints'
        ]);
    }

    public function new() :Response
    {
        if (!$this->getAuthenticator()->isLoggedIn())
            return $this->redirectToRoute('index');

        if ($this->getAuthenticator()->isLecturer())
            return $this->redirectToRoute('complaints');

        $errorList = [];

        $date = date('Y-m-d h:i:sa');
        $title = $this->post->get('title');
        $author = $this->getProfile()->getUsername();
        $message = $this->post->get('message');

        if ($this->getRequest()->isMethod('post'))
        {
            if (empty(($title || $message)))
                $errorList[] = 'All fields are required';

            if (empty($errorList))
            {
                $complaint = new Complaint(0, $title, $author, $message, $date);

                if ($this->complaintStorage->save($complaint))
                {
                    $this->getSession()->set('saveSuccess', 'Complaint has been sent to our admins. Thanks');
                    return $this->redirectToRoute('newComplaint');
                }
                else
                    $errorList[] = 'Internal server error. Please try again later';
            }
        }

        return $this->renderTemplate('complaints/new.html.twig', [
            'title' => $title,
            'errors' => $errorList,
            'message' => $message,
            'success' => $this->getSession()->flash('saveSuccess'),
            'pageTitle' => 'Add new complaint',
        ]);
    }

    public function view($id = '') :Response
    {
        if (!$this->getAuthenticator()->isLoggedIn())
            return $this->redirectToRoute('index');

        $this->getAuthenticator()->requireAdmin();

        $complaint = $this->getComplaint((int)$id ?? 0);

        if ($complaint->isSaved())
        {
            return $this->renderTemplate('complaints/view.html.twig', [
                'id' => $complaint->getIdentifier(),
                'title' => $complaint->getTitle(),
                'author' => $complaint->getAuthor(),
                'message' => $complaint->getMessage(),
                'pageTitle' => $complaint->getTitle()
            ]);
        }
        return $this->redirectToRoute('complaints');
    }

    public function delete($id = '') :Response
    {
        if (!$this->getAuthenticator()->isLoggedIn())
            return $this->redirectToRoute('index');

        $this->getAuthenticator()->requireAdmin();

        $complaint = $this->getComplaint((int)$id ?? 0);

        if ($complaint->isSaved())
        {
            if ($this->complaintStorage->delete($complaint))
            {
                $this->getSession()->set('deleteSuccess', 'Complaint deleted successfully');
                return $this->redirectToRoute('complaints');
            }
            return $this->redirectToRoute('complaints');
        }
        return $this->redirectToRoute('complaints');
    }

    private function getComplaint(int $id = 0) :Complaint
    {
        return $this->complaintStorage->getById($id);
    }
}
