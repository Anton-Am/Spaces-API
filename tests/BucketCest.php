<?php

namespace Tests;

use Exception;

/**
 * Class BucketCest
 *
 * @package Tests
 */
class BucketCest
{
    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function created(UnitTester $I): void
    {

    }


    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function exists(UnitTester $I): void
    {

    }


    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function notExists(UnitTester $I): void
    {

    }

    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function downloaded(UnitTester $I): void
    {
        // Upload file
        $file = realpath(__DIR__ . '/../composer.json');
        $I->uploadFile($file);
        $I->assertUploadedFileExists($file);

        // Upload dir
        $directory = realpath(__DIR__ . '/_output');
        $I->downloadBucket($directory);
        $I->assertFileExists($directory . $file);
    }

    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function aclGot(UnitTester $I): void
    {

    }

    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function aclUpdated(UnitTester $I): void
    {

    }


    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function fileListGot(UnitTester $I): void
    {

    }

    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function corsGot(UnitTester $I): void
    {

    }

    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function corsUpdated(UnitTester $I): void
    {

    }

    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function deleted(UnitTester $I): void
    {

    }
}
