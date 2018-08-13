<?php
include_once(__DIR__ . '/MicroweberHooks.php');

class MicroweberPluginController
{
    private $cpanel;

    public function __construct($cpanel)
    {
        $this->cpanel = $cpanel;
    }

    public function install()
    {

        $adminEmail = $_POST['admin_email'];
        $adminUsername = $_POST['admin_username'];
        $adminPassword = $_POST['admin_password'];
        $dbDriver = 'mysql';
        $dbHost = 'localhost';
        $dbName = '';
        $dbUsername = '';
        $dbPassword = $this->randomPassword();
        $dbPrefix = '';

        // Prepare data
        $domainData = json_decode($_POST['domain']);
        $installPath = $domainData->documentroot;
        $domainData = json_decode($_POST['domain']);
        $dbUsername = $this->getUsername();
        $dbPrefix = $this->getDBPrefix();
        $dbNameLength = 16 - strlen($dbPrefix);
        $dbName = str_replace('.', '_', $domainData->domain);
        $dbName = $dbPrefix . substr($dbName, 0, $dbNameLength);
        $dbUsername = $dbName;
        $dbHost = $this->cpanel->uapi('Mysql', 'locate_server');
        $dbHost = $dbHost['cpanelresult']['result']['data']['remote_host'];
        if ($_POST['express'] == '0') {
            $dbDriver = $_POST['db_driver'];
            $dbHost = $_POST['db_host'];
            $dbName = $_POST['db_name'];
            $dbUsername = $_POST['db_username'];
            $dbPassword = $_POST['db_password'];
        }

        $domain = $domainData->domain;
        $installPath = $domainData->documentroot;

        $version_manager = new MicroweberVersionsManager($installPath);
        if (!$version_manager->hasDownloaded()) {
            $version_manager->download();
        }
        if (!$version_manager->hasDownloaded()) {
            return;
        }

        $dbPassword = $this->randomPassword();


        $opts = array();
        $opts['source_folder'] = $installPath;
        $opts['user'] = $adminUsername;
        $opts['pass'] = $adminPassword;
        $opts['email'] = $adminEmail;
        $opts['database_driver'] = $dbDriver;
        $opts['database_user'] = $dbUsername;
        $opts['database_password'] = $dbPassword;
        $opts['database_table_prefix'] = $dbPrefix;
        $opts['database_name'] = $dbName;
        $opts['database_host'] = $dbHost;
        $opts['default_template'] = 'dream'; //@todo get from settings

      
//        $install_opts = array();
//        $opts['options'] = $install_opts;
        $do_install = new MicroweberInstallCommand();
        $do_install = $do_install->install($opts);


        // Create database
//        $this->cpanel->uapi('Mysql', 'create_database', array('name' => $dbName));
//        $this->cpanel->uapi('Mysql', 'create_user', array('name' => $dbUsername, 'password' => $dbPassword));
//        $this->cpanel->uapi('Mysql', 'set_privileges_on_database', array('user' => $dbUsername, 'database' => $dbName, 'privileges' => 'ALL PRIVILEGES'));

        // Create empty install directory
//        exec("rm -rf $installPath");
//        mkdir($installPath);
//
//        // Download install zip
//        copy($zipInstallUrl, $zipInstallPath);
//        exec("unzip $zipInstallPath -d $installPath");
//
//        // Download userfiles zip
//        copy($zipUserfilesUrl, $zipUserfilesPath);
//        exec("unzip $zipUserfilesPath -d $installPath");
//
//        // Permissions
//        exec("chmod -R 755 $installPath");
//
//        // Clear cache
//        exec("php $installPath/artisan cache:clear");
//
//        // Install Microweber
//        $installCommand = "php $installPath/artisan microweber:install $adminEmail $adminUsername $adminPassword $dbHost $dbName $dbUsername $dbPassword $dbDriver -p $dbPrefix -t dream -d 1 -c 1";
//        file_put_contents('/tmp/install_command', $installCommand);
//        exec($installCommand);
//
//        return compact('adminEmail', 'adminUsername', 'adminPassword');
    }


    public function OOOOLD___install()
    {
        $installPath = '/var/www/microweber';
        $zipInstallUrl = 'http://download.microweberapi.com/ready/core/microweber-latest.zip';
        $zipInstallPath = '/tmp/microweber-latest.zip';
        $zipUserfilesUrl = 'https://members.microweber.com/_partners/csigma/userfiles.zip';
        $zipUserfilesPath = '/tmp/userfiles.zip';
        $adminEmail = $_POST['admin_email'];
        $adminUsername = $_POST['admin_username'];
        $adminPassword = $_POST['admin_password'];
        $dbDriver = 'mysql';
        $dbHost = 'localhost';
        $dbName = '';
        $dbUsername = '';
        $dbPassword = $this->randomPassword();
        $dbPrefix = '';

        // Prepare data
        $domainData = json_decode($_POST['domain']);
        $installPath = $domainData->documentroot;
        $domainData = json_decode($_POST['domain']);
        $dbUsername = $this->getUsername();
        $dbPrefix = $this->getDBPrefix();
        $dbNameLength = 16 - strlen($dbPrefix);
        $dbName = str_replace('.', '_', $domainData->domain);
        $dbName = $dbPrefix . substr($dbName, 0, $dbNameLength);
        $dbUsername = $dbName;
        $dbHost = $this->cpanel->uapi('Mysql', 'locate_server');
        $dbHost = $dbHost['cpanelresult']['result']['data']['remote_host'];
        if ($_POST['express'] == '0') {
            $dbDriver = $_POST['db_driver'];
            $dbHost = $_POST['db_host'];
            $dbName = $_POST['db_name'];
            $dbUsername = $_POST['db_username'];
            $dbPassword = $_POST['db_password'];
        }

        // Create database
        $this->cpanel->uapi('Mysql', 'create_database', array('name' => $dbName));
        $this->cpanel->uapi('Mysql', 'create_user', array('name' => $dbUsername, 'password' => $dbPassword));
        $this->cpanel->uapi('Mysql', 'set_privileges_on_database', array('user' => $dbUsername, 'database' => $dbName, 'privileges' => 'ALL PRIVILEGES'));

        // Create empty install directory
        exec("rm -rf $installPath");
        mkdir($installPath);

        // Download install zip
        copy($zipInstallUrl, $zipInstallPath);
        exec("unzip $zipInstallPath -d $installPath");

        // Download userfiles zip
        copy($zipUserfilesUrl, $zipUserfilesPath);
        exec("unzip $zipUserfilesPath -d $installPath");

        // Permissions
        exec("chmod -R 755 $installPath");

        // Clear cache
        exec("php $installPath/artisan cache:clear");

        // Install Microweber
        $installCommand = "php $installPath/artisan microweber:install $adminEmail $adminUsername $adminPassword $dbHost $dbName $dbUsername $dbPassword $dbDriver -p $dbPrefix -t dream -d 1 -c 1";
        file_put_contents('/tmp/install_command', $installCommand);
        exec($installCommand);

        return compact('adminEmail', 'adminUsername', 'adminPassword');
    }

    public function uninstall()
    {
        // Prepare data
        $domainData = json_decode($_POST['domain']);
        $installPath = $domainData->documentroot;
        $dbUsername = $this->getUsername();
        $dbPrefix = $this->getDBPrefix();
        $dbNameLength = 16 - strlen($dbPrefix);
        $dbName = str_replace('.', '_', $domainData->domain);
        $dbName = $dbPrefix . substr($dbName, 0, $dbNameLength);
        $dbUsername = $dbName;

        // Create empty install directory
        exec("rm -rf $installPath");
        mkdir($installPath);

        // Delete database
        $this->cpanel->uapi('Mysql', 'delete_database', array('name' => $dbName));
        $this->cpanel->uapi('Mysql', 'delete_user', array('name' => $dbUsername));
    }

    public function getUsername()
    {
        $username = $this->cpanel->exec('<cpanel print="$user">');
        return $username['cpanelresult']['data']['result'];
    }

    public function getDBPrefix()
    {
        return substr($this->getUsername(), 0, 8) . '_';
    }

    private function randomPassword($length = 32)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}