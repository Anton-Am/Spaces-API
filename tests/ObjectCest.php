<?php

namespace Tests;

use Exception;

/**
 * Class ObjectCest
 *
 * @package Tests
 */
class ObjectCest
{
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
    public function directoryCreated(UnitTester $I): void
    {

    }

    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function directoryCreatedWithFiles(UnitTester $I): void
    {

    }

    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function fileCreated(UnitTester $I): void
    {
        $file = realpath(__DIR__ . '/../composer.json');
        $I->uploadFile($file);
        $I->assertUploadedFileExists($file);
    }

    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function fileTempUrlCreated(UnitTester $I): void
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
    public function directoryFileList(UnitTester $I): void
    {

    }

    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function downloaded(UnitTester $I): void
    {

    }

    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function downloadedToLocalPath(UnitTester $I): void
    {

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
    public function makePrivate(UnitTester $I): void
    {

    }

    /**
     * @param UnitTester $I
     * @throws Exception
     */
    public function makePublic(UnitTester $I): void
    {

    }
}
