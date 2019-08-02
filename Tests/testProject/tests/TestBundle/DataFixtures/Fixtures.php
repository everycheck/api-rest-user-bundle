<?php  
	
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__."/../../../../../vendor/autoload.php";


use EveryCheck\UserApiRestBundle\Service\Api;

class Fixtures
{
	public function execute($param)
    {
        $this->logCount = 1;

        $this->api = new Api($param['url'],$param['login'],$param['password']);
        $token = $this->api->getUserToken($isBasic = true);
        $this->api->setUpToken($token->value);

        $admin = $this->api->getCurrentUser();

        $this->store($admin->uuid, "user_admin_");
        $this->store($token->value, "token_admin_");

        $this->createUser("user","creator");
        $this->createUser("user","reader");
        $this->createUser("user","updator");
        $this->createUser("user","deletor");

        $this->createUser("role","creator");
        $this->createUser("role","reader");
        $this->createUser("role","updator");
        $this->createUser("role","deletor");

        $this->createUser("none","none");

        $this->createUser("role","deletor",'2_');

        copy(__DIR__."/../../../var/data/db_test/user_testing.sqlite", __DIR__."/../../../var/data/db_test/LoadFixture.sqlite");
        file_put_contents(__DIR__."/env.json", json_encode($this->env,JSON_PRETTY_PRINT));
    }

    private function store($object, $prefix)
    {
        $this->env[$prefix.$this->logCount] = $object;
        $this->logCount++;
    }

    public function createUser($table, $action,$suffix = "")
    {
        $username = 'user_'.$table.'_'.$action.$suffix;

        $client = $this->api->postUser([            
            "username" => $username,
            "email" => $username.'@everycheck.com'
        ]);
        $this->store($client->uuid, $username.'_');

        $role = $this->api->postRole($client->uuid,[            
            "name" => $table,
            "creator" => $action == 'creator' ? 1:0,
            "reader"  => $action == 'reader'  ? 1:0,
            "updator" => $action == 'updator' ? 1:0,
            "deletor" => $action == 'deletor' ? 1:0,
        ]);
        $this->store($role->uuid, "role_");

        $response = $this->api->postToken([            
            "login" => $username,
            "password" => $username.'@everycheck.com',
            "newPassword" =>'1Azertyu'
        ],'basic');
        $token = json_decode($response->getBody()->getContents());
        $this->store($token->value,'token_'.$table.'_'.$action.'_');

        $this->api->postToken([            
            "login" => $username,
            "password" =>'1Azertyu'
        ]);

        return $client;
    }
}

try
{
	if(count($argv) < 4)
	{
		throw new \Exception("Usage : \n command url login password");
	}
	$arguments = [
		"url"    => $argv[1],
		"login"    => $argv[2],
		"password" => $argv[3],
	];

	$test = new Fixtures();
	$test->execute($arguments);
}
catch(\Exception $e)
{
	echo $e->getMessage();
	echo "\n";
}