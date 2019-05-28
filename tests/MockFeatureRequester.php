<?php
namespace LaunchDarkly\Tests;

use LaunchDarkly\FeatureRequester;

class MockFeatureRequester implements FeatureRequester
{
    public static $flags = array();

    public function __construct($baseurl, $key, $options)
    {
    }

    public function getFeature($key)
    {
        return isset(self::$flags[$key]) ? self::$flags[$key] : null;
    }

    public function getSegment($key)
    {
        return null;
    }

    public function getFeatures($keys)
    {
        $filteredFlags = [];
        foreach (self::$flags as $key => $value) {
            if (in_array($key, $keys)) {
                $filteredFlags[$key] = $value;
            }
        }

        return $filteredFlags;
    }

    public function getAllFeatures()
    {
        return self::$flags;
    }
}
