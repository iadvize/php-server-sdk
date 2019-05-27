<?php

namespace LaunchDarkly\Tests;

use LaunchDarkly\Integrations\Redis;
use Predis\Client;

class RedisFeatureRequesterTest extends FeatureRequesterTestBase
{
    const PREFIX = 'test';

    /** @var ClientInterface */
    private static $predisClient;
    
    public static function setUpBeforeClass()
    {
        if (!static::isSkipDatabaseTests()) {
            self::$predisClient = new Client(array());
        }
    }

    protected function isDatabaseTest()
    {
        return true;
    }
    
    protected function makeRequester()
    {
        $factory = Redis::featureRequester();
        return $factory('', '', array('redis_prefix' => self::PREFIX));
    }

    protected function putItem($namespace, $key, $version, $json)
    {
        self::$predisClient->hset(self::PREFIX . ":$namespace", $key, $json);
    }

    protected function deleteExistingData()
    {
        self::$predisClient->flushdb();
    }

    public function testGetFeatures()
    {
        $flagKey1 = 'foo';
        $flagKey2 = 'bar';
        $flagKey3 = 'deleted';
        $flagVersion = 10;
        $flagJson1 = self::makeFlagJson($flagKey1, $flagVersion);
        $flagJson2 = self::makeFlagJson($flagKey2, $flagVersion);
        $flagJson3 = self::makeFlagJson($flagKey3, $flagVersion, true);

        $this->putItem('features', $flagKey1, $flagVersion, $flagJson1);
        $this->putItem('features', $flagKey2, $flagVersion, $flagJson2);
        $this->putItem('features', $flagKey3, $flagVersion, $flagJson3);

        $fr = $this->makeRequester();
        $flags = $fr->getFeatures([
            $flagKey1,
            $flagKey2,
        ]);

        $this->assertEquals(2, count($flags));
        $flag1 = $flags[$flagKey1];
        $this->assertEquals($flagKey1, $flag1->getKey());
        $this->assertEquals($flagVersion, $flag1->getVersion());
        $flag2 = $flags[$flagKey2];
        $this->assertEquals($flagKey2, $flag2->getKey());
        $this->assertEquals($flagVersion, $flag2->getVersion());
    }
}
