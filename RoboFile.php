<?php

require_once('vendor/autoload.php');
if (file_exists(__DIR__.'/.env')) {
    Dotenv::load(__DIR__);
}

class RoboFile extends \Robo\Tasks
{
    use \Robo\Task\Development\loadTasks;
    use \Robo\Common\TaskIO;

    public function __construct()
    {
        foreach ($_ENV as $option => $value) {
            if (substr($option, 0, 3) === 'OC_') {
                $option = strtolower(substr($option, 3));
                $this->config[$option] = $value;
            }
        }
        $required = array('db_username', 'password', 'email', 'http_server');
        $missing = array();
        foreach ($required as $config) {
            if (empty($this->config[$config])) {
                $missing[] = 'OC_'.strtoupper($config);
            }
        }
        if (!empty($missing)) {
            $this->printTaskError("<error> Missing ".implode(', ', $missing));
            $this->printTaskError("<error> See .env.sample ");
            die();
        }
    }

    public function opencartSetup()
    {
        $this->taskDeleteDir('www')->run();
        $this->taskFileSystemStack()
            ->mirror('vendor/opencart/opencart/upload', 'www')
            ->chmod('www', 0777, 0000, true)
            ->run();

        // Create new database, drop if is already
        try {
            $conn = new PDO("mysql:host=".$this->config['db_hostname'], $this->config['db_username'], $this->config['db_password']);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->exec("DROP DATABASE IF EXISTS " . $this->config['db_database']);
            $conn->exec("CREATE DATABASE " . $this->config['db_database']);
        }
        catch(PDOException $e)
        {
            $this->printTaskError("<error> Could not connect ot database...");
        }
        $conn = null;

        $install = $this->taskExec('php')->arg('www/install/cli_install.php')->arg('install');
        foreach ($this->config as $option => $value) {
            $install->option($option, $value);
        }
        $install->run();
        $this->taskDeleteDir('www/install')->run();
    }

    public function opencartRun()
    {
        $this->taskServer(8000)
            ->dir('www')
            ->run();
    }

    public function projectDeploy()
    {
        $this->taskFileSystemStack()
            ->mirror('src/upload', 'www')
            ->copy('src/install.xml','www/system/install.ocmod.xml')
            ->run();
    }

    public function projectWatch()
    {
        $this->projectDeploy();

        $this->taskWatch()
            ->monitor('composer.json', function () {
                $this->taskComposerUpdate()->run();
                $this->taskWatch();
            })->monitor('src/', function () {
                $this->taskWatch();
            })->run();
    }

    public function projectPackage()
    {
        $this->taskDeleteDir('target')->run();
        $this->taskFileSystemStack()->mkdir('target')->run();

        $zip = new ZipArchive();
        $filename = "target/build.ocmod.zip";

        if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
            $this->printTaskError("<error> Could not create ZipArchive");
            exit();
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator("src", \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->isReadable()) {
                $zip->addFile($file->getPathname(),substr($file->getPathname(),4));
            }
        }

        $zip->close();
    }

}