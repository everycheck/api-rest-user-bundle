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

        $this->api = new Api($param['url'],$param['login'],$param['password'],false);
        $token = $this->api->getUserToken($isBasic = true);
        $this->api->setUpToken($token->value);

        $this->store($token->value, "token_");

        $user = $this->createUser();

        copy(__DIR__."/../../../var/data/db_test/user_testing.sqlite", __DIR__."/../../../var/data/db_test/LoadFixture.sqlite");
        file_put_contents(__DIR__."/env.json", json_encode($this->env,JSON_PRETTY_PRINT));
    }

    private function store($object, $prefix)
    {
        $this->env[$prefix.$this->logCount] = $object;
        $this->logCount++;
    }

    public function createUser()
    {
        $client = $this->api->postUser([            
            "username" => 'someone',
            "email" => 'someone@everycheck.com'
        ]);
        $this->store($client->uuid, "user_");

        $role = $this->api->postRole($client->uuid,[            
            "name" => "somerole",
        ]);
        $this->store($role->uuid, "role_");

        $this->api->postToken([            
            "login" => 'someone',
            "password" => 'someone@everycheck.com',
            "newPassword" =>'1Azertyu'
        ]);

        $this->api->postToken([            
            "login" => 'someone',
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