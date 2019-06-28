<?php
namespace EveryCheck\UserApiRestBundle\Tests\TestBundle\EndToEnd;

use EveryCheck\TestApiRestBundle\Controller\JsonApiAsArrayTestCase;
use EveryCheck\TestApiRestBundle\Loader\ResourcesFileLoader;

class NotFoundTest extends JsonApiAsArrayTestCase
{
    /**
    * @dataProvider ApiCallProvider
    */
    public function testAPICall($data_test)
    {
        $this->genericTestAPICall($data_test);
    }

    public static function ApiCallProvider()
    {   
        return ResourcesFileLoader::testCaseProvider(__DIR__,'not_found');
    }  

    public function setUp($fixtureFilename = null)
    {
        parent::setUp("LoadFixture");
    }
}