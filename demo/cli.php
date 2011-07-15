<?php 

use ClassQL\Loader,
    ClassQL\Session,
    ClassQL\CLI,
    Demo\User,
    Demo\Message;

// ----------------------------------------------------------
// Class loader

require_once __DIR__ . '/../libs/ClassQL/Loader.php';
Loader::register('Parsec', __DIR__ . '/../vendor/parsec/lib/Parsec');
Loader::register('ClassQL', __DIR__ . '/../libs/ClassQL');
Loader::register('Demo\Models', __DIR__ . '/libs/Models', true);
Loader::register('Demo', __DIR__ . '/libs');

// ----------------------------------------------------------
// Demo commands

class DemoCLI extends CLI
{
    public function execute($args, $opts)
    {
        if (isset($opts['profile'])) {
            Session::getConnection()->setProfiler(
                new ClassQL\Database\FileProfiler('queries.log'));
        }
        parent::execute($args, $opts);
    }
    
    public function executeClearAll($args, $opts)
    {
        User::truncate();
        Message::truncate();
    }
    
    public function executeCompileTime($args, $opts)
    {
        $cacheStatus = Session::getCache() !== null ? "cache enabled" : "without cache";
        $this->println("Including models with $cacheStatus");
        $start = microtime(true);
        
        $user = new User();
        $message = new Message();
        
        $time = microtime(true) - $start;
        $this->println("Execution time: $time");
    }
    
    public function executeAddUser($args, $opts)
    {
        $opts = array_merge(
            array(
                'email' => 'example@example.com', 
                'firstname' => 'john', 
                'lastname' => 'doe', 
                'password' => uniqid()
            ),
            $opts
        );
        
        $user = new User();
        $user->email = $opts['email'];
        $user->firstName = $opts['firstname'];
        $user->lastName = $opts['lastname'];
        $user->password = $opts['password'];
        $user->insert();
    }
    
    public function executeUpdateUser($args, $opts)
    {
        if (!isset($opts['id']) || ($user = User::find($opts['id'])) === false) {
            $this->println("User not found");
            exit;
        }
        
        if (!empty($opts['email'])) $user->email = $opts['email'];
        if (!empty($opts['firstname'])) $user->firstName = $opts['firstname'];
        if (!empty($opts['lastname'])) $user->lastName = $opts['lastname'];
        if (!empty($opts['password'])) $user->password = $opts['password'];
        
        $user->update();
    }
    
    public function executePostMessage($args, $opts)
    {
        if (!isset($opts['to']) || ($user = User::find($opts['to'])) === false) {
            $this->println("User not found");
            exit;
        }
        
        $user->addMessage($opts['message']);
    }
    
    public function executeShow($args, $opts)
    {
        $users = User::findAllWithMessages();
        foreach ($users as $user) {
            echo "#$user->id: $user->firstName $user->lastName ($user->email)\n";
            foreach ($user->messages as $message) {
                echo "\t#$message->id: $message->message\n";
            }
        }
    }
}

// ----------------------------------------------------------
// ClassQL session initialization

Session::start(array(
    'dsn' => 'sqlite:demo.db',
    'streamcache' => __DIR__ . '/cache/cql',
    'cache' => new ClassQL\Cache\File(__DIR__ . '/cache/db')
));

// ----------------------------------------------------------
// Run CLI

CLI::register('demo', 'DemoCLI');
CLI::run();
