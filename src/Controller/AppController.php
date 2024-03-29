<?php

namespace App\Controller;


use App\Models\Profile;
use App\Core\Token\Token;
use App\Core\Database\Database;
use App\Storages\StorageManager;
use App\Core\Session\AppSession;
use App\Core\DataTable\DataTable;
use App\Core\Collection\AppCollection;
use App\Core\Configuration\Configuration;
use App\Core\Authenticator\Authenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AppController extends AbstractController
{
	protected static $ds = DIRECTORY_SEPARATOR;

	protected function getRequest() :Request
	{
		return Request::createFromGlobals();
	}

	protected function getPost() :ParameterBag
	{
		return $this->getRequest()->request;
	}

	protected function getGet() :ParameterBag
	{
		return $this->getRequest()->query;
	}

	protected function getSession() :AppSession
	{
        if (!isset($_SESSION))
            session_start();
        return new AppSession;
	}

	protected function getServer() :ServerBag
	{
		return $this->getRequest()->server;
	}

	protected function getConfiguration() :Configuration
	{
		return new Configuration($this->getRootDir());
	}

	protected function getDatabase() :Database
	{
		$db = self::getDatabaseConfig();
        $port = $db->getString('port');
		$driver = $db->getString('driver');
		$hostname = $db->getString('hostname');
		$database = $db->getString('database');
		$username = $db->getString('username');
		$password = $db->getString('password');
		$connectionString = "$driver:host=$hostname;dbname=$database;port=$port";
		return Database::getInstance($connectionString, $username, $password);
	}

	protected function getRootDir() :string
	{
		return __DIR__ . self::$ds . '..' . self::$ds . '..' . self::$ds;
	}

	protected function getDatabaseConfig() :AppCollection
	{
		return new AppCollection(self::getConfiguration()->getData()['database']);
	}

	protected function getResponse($content = '', int $status = 200, array $headers = []) :Response
	{
		return new Response($content, $status, $headers);
	}

	protected function getStorageManager() :StorageManager
	{
		return new StorageManager($this->getDatabase());
	}

	protected function getAuthenticator() :Authenticator
	{
		return new Authenticator($this->getStorageManager()->getProfileStorage(), $this->getSession());
	}

	protected function getToken() :Token
    {
        return new Token($this->getSession());
    }

	protected function getProfile() :Profile
    {
        $userId = $this->getSession()->getInt('identifier');
        return $this->getStorageManager()->getProfileStorage()->getById($userId);
    }

    protected function renderTemplate(string $view = '', array $parameters = []) :Response
    {
        return $this->render($view, array_merge($parameters, $this->commonParameters()));
    }

    protected function getDataTable() :DataTable
    {
        return new DataTable($this->getDatabaseConfig());
    }

    private function commonParameters() :array
    {
        return [
            'admin' => $this->getAuthenticator()->isAdmin(),
            'student' => $this->getAuthenticator()->isLoggedIn(),
            'footNote' => 'SIS 2017',
            'lecturer' => $this->getAuthenticator()->isLecturer(),
            'headAdmin' => $this->getAuthenticator()->isHeadAdmin(),
            'profileId' => $this->getProfile()->getIdentifier(),
            'brandName' => 'SIS',
            'brandName2' => 'SIS',
            'profileRole' => $this->getProfile()->getRole(),
            'profileUsername' => $this->getProfile()->getUsername(),
        ];
    }
}
