<?php

include_once(__DIR__ . '/MicroweberStorage.php');
include_once(__DIR__ . '/MicroweberVersionsManager.php');
include_once(__DIR__ . '/MicroweberInstallCommand.php');

class MicroweberHooks
{
    private $input;
    private $storage;

    public function __construct($input=false)
    {
        $this->input = $input;
        $this->storage = new MicroweberStorage();
    }

    // Embed hook attribute information.
    public function describe()
    {
        $add_account = array(
            'category' => 'Whostmgr',
            'event' => 'Accounts::Create',
            'stage' => 'post',
            'hook' => '/var/cpanel/microweber/mw_hooks.php --add-account',
            'exectype' => 'script',
        );
        return json_encode(array($add_account));
    }

    public function add_account()
    {
        if (!$this->checkIfAutoInstall()) return;
        if (!$this->checkIfFeatureEnabled()) return;
        $domain = $this->input->data->args->domain;
        $installPath = $this->input->data->args->homedir;
        $adminEmail = $this->input->data->args->contactemail;
        $adminUsername = $this->input->data->user;
        $adminPassword = $this->input->data->args->password;
        $this->install($domain, $installPath, $adminEmail, $adminUsername, $adminPassword);
    }


    // ----------------------

    public function install($domain, $installPath, $adminEmail, $adminUsername, $adminPassword, $dbHost = 'localhost', $dbDriver = 'mysql')
    {

        $source_folder = '/usr/share/microweber/latest/';

        $source_folder =$installPath;


        $version_manager = new MicroweberVersionsManager($source_folder);
        if(!$version_manager->hasDownloaded()){
            $version_manager->download();
        }
        if(!$version_manager->hasDownloaded()){
            return;
        }


        //$dbDriver @todo get from settings ?



        $dbPrefix = substr($adminUsername, 0, 8) . '_';
        $dbNameLength = 16 - strlen($dbPrefix);
        $dbName = str_replace('.', '_', $domain);
        $dbName = $dbPrefix . substr($dbName, 0, $dbNameLength);
        $dbUsername = $dbName;
        //$dbHost = $this->cpanel->uapi('Mysql', 'locate_server');
        //$dbHost = $dbHost['cpanelresult']['result']['data']['remote_host'];
        $dbPass = $this->randomPassword();

        // Create database
        $this->execUapi('Mysql', 'create_database', array('name' => $dbName));
        $this->execUapi('Mysql', 'create_user', array('name' => $dbUsername, 'password' => $dbPass));
        $this->execUapi('Mysql', 'set_privileges_on_database', array('user' => $dbUsername, 'database' => $dbName, 'privileges' => 'ALL PRIVILEGES'));


        $opts = array();
        $opts['user'] = $adminUsername;
        $opts['pass'] = $adminPassword;
        $opts['email'] = $adminEmail;
        $opts['database_driver'] = $dbDriver;
        $opts['database_user'] = $dbUsername;
        $opts['database_password'] = $dbPass;
        $opts['database_table_prefix'] = $dbPrefix;
        $opts['database_name'] = $dbName;
        $opts['default_template'] = 'dream'; //@todo get from settings
        $opts['source_folder'] = $source_folder;
        $opts['is_symliked'] = true;
        $opts['debug_email'] = 'boksiora@gmail.com'; //@todo get from settings
//        $install_opts = array();
//        $opts['options'] = $install_opts;
        $do_install = new MicroweberInstallCommand();
        $do_install = $do_install->install($opts);


        return compact('adminEmail', 'adminUsername', 'adminPassword');
    }


    private function OOOOLD_install($domain, $installPath, $adminEmail, $adminUsername, $adminPassword, $dbHost = 'localhost', $dbDriver = 'mysql')
    {

        $source_folder = '/usr/share/microweber/latest/';


        // Prepare data
        $zipInstallUrl = 'http://download.microweberapi.com/ready/core/microweber-latest.zip';
        $zipInstallPath = '/tmp/microweber-latest.zip';
        $zipUserfilesUrl = 'https://members.microweber.com/_partners/csigma/userfiles.zip';
        $zipUserfilesPath = '/tmp/userfiles.zip';


        $dbPrefix = substr($adminUsername, 0, 8) . '_';
        $dbNameLength = 16 - strlen($dbPrefix);
        $dbName = str_replace('.', '_', $domain);
        $dbName = $dbPrefix . substr($dbName, 0, $dbNameLength);
        $dbUsername = $dbName;
        $dbHost = $this->cpanel->uapi('Mysql', 'locate_server');
        $dbHost = $dbHost['cpanelresult']['result']['data']['remote_host'];
        $dbPass = $this->randomPassword();

        // Create database
        $this->execUapi('Mysql', 'create_database', array('name' => $dbName));
        $this->execUapi('Mysql', 'create_user', array('name' => $dbUsername, 'password' => $dbPass));
        $this->execUapi('Mysql', 'set_privileges_on_database', array('user' => $dbUsername, 'database' => $dbName, 'privileges' => 'ALL PRIVILEGES'));

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
        exec("chown $adminUsername $installPath");

        // Clear cache
        exec("php $installPath/artisan cache:clear");

        // $opts['user'];
        // $opts['pass'];
        // $opts['email'];
        // $opts['database_driver'];
        // $opts['database_name'];
        // $opts['database_user'];
        // $opts['database_password'];
        // $opts['database_table_prefix'];
        // $opts['default_template'];
        // $opts['source_folder'];
        // $opts['is_symliked'];
        // $opts['debug_email'];
        // $opts['debug_email_subject'];
        // $opts['install_debug_file'];
        // $opts['options'];
        // $opts['options'][0]['option_key'];


        $opts = array();
        $opts['user'] = $adminUsername;
        $opts['pass'] = $adminPassword;
        $opts['email'] = $adminEmail;
        $opts['database_driver'] = $dbDriver;
        $opts['database_user'] = $dbUsername;
        $opts['database_password'] = $dbPass;
        $opts['database_table_prefix'] = $dbPrefix;
        $opts['database_name'] = $dbName;
        $opts['default_template'] = 'dream';
        $opts['source_folder'] = $source_folder;

        $opts['is_symliked'] = true;
        $opts['debug_email'] = 'boksiora@gmail.com';

        $install_opts = array();
        $opts['options'] = $install_opts;


        $do_install = new MicroweberInstallCommand();
        $do_install = $do_install->install($opts);


        // Install Microweber
        $installCommand = "php $installPath/artisan microweber:install $adminEmail $adminUsername $adminPassword $dbHost $dbName $dbUsername $adminPassword $dbDriver -p $dbPrefix -t dream -d 1 -c 1";
        file_put_contents('/tmp/install_command', $installCommand);
        exec($installCommand);

        return compact('adminEmail', 'adminUsername', 'adminPassword');
    }

    // ----------------------

    private function checkIfFeatureEnabled()
    {
        $user = $this->input->data->user;
        $account = $this->execApi1('accountsummary', compact('user'));
        $account = $account->data->acct[0];
        $pkg = $account->plan;
        $package = $this->execApi1('getpkginfo', compact('pkg'));
        $package = $package->data->pkg;
        $featurelist = $package->FEATURELIST;
        $featurelistData = $this->execApi1('get_featurelist_data', compact('featurelist'));
        $featureHash = $featurelistData->data->features;
        foreach ($featureHash as $hash) {
            if ($hash->id == 'microweber') {
                return $hash->is_disabled == '0';
            }
        }
        return false;
    }

    private function checkIfAutoInstall()
    {
        $config = $this->storage->read();
        return isset($config->auto_install) && $config->auto_install;
    }

    private function checkIfSymlinkInstall()
    {
        $config = $this->storage->read();
        return isset($config->install_type) && $config->install_type == 'symlinked';
    }

    private function execUapi($module, $function, $args = array())
    {
        $user = $this->input->data->user;
        $argsString = '';
        foreach ($args as $key => $value) {
            $argsString = urlencode($key) . '=' . urlencode($value);
        }
        $command = "uapi --user=$user --output=json $module $function $argsString";
        $json = shell_exec($command);
        return json_decode($json);
    }

    private function execApi1($function, $args)
    {
        $argsString = '';
        foreach ($args as $key => $value) {
            $argsString = urlencode($key) . '=' . urlencode($value);
        }
        $command = "whmapi1 --output=json $function $argsString";
        $json = shell_exec($command);
        return json_decode($json);
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
