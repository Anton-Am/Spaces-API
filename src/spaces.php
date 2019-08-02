<?php

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;


/**
 * Class SpacesConnect
 * An API wrapper for AWS, makes working with DigitalOcean's Spaces super easy.
 * Written by Devang Srivastava for Dev Uncoded.
 * Fixed and featured by Anton Am.
 *
 * Available under MIT License ( https://opensource.org/licenses/MIT )
 */
class SpacesConnect
{
    private $client;
    private $space;
    private $accessKey;
    private $secretKey;
    private $host;
    private $region;

    /**
     * SpacesConnect constructor.
     *
     * @param $accessKey
     * @param $secretKey
     * @param string $spaceName
     * @param string $region
     * @param string $host
     * @throws SpacesAPIException
     */
    public function __construct($accessKey, $secretKey, $spaceName = '', $region = 'nyc3', $host = 'digitaloceanspaces.com')
    {
        if (!empty($spaceName)) {
            $endpoint = 'https://' . $spaceName . '.' . $region . '.' . $host;
        } else {
            $endpoint = 'https://' . $region . '.' . $host;
        }

        if (!class_exists(S3Client::class)) {
            throw new SpacesAPIException(json_encode([
                'error' => [
                    'message' => 'No AWS class loaded',
                    'code'    => 'no_aws_class',
                    'type'    => 'init'
                ]
            ]));
        }
        try {
            $this->client = S3Client::factory([
                'region'            => $region,
                'version'           => 'latest',
                'endpoint'          => $endpoint,
                'credentials'       => [
                    'key'    => $accessKey,
                    'secret' => $secretKey,
                ],
                'bucket_endpoint'   => true,
                'signature_version' => 'v4-unsigned-body'
            ]);
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
        $this->space = $spaceName;
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->host = $host;
        $this->region = $region;
    }


    /**
     * @param $spaceName
     * @return array|mixed
     * @throws S3Exception
     */
    public function createSpace($spaceName = null)
    {
        $spaceName = $spaceName ?? $this->space;
            $this->setSpace($spaceName);
            $success = $this->client->createBucket(['Bucket' => $spaceName]);
            $this->client->waitUntil('BucketExists', ['Bucket' => $spaceName]);

            return $this->objReturn($success->toArray());
    }


    /**
     * Lists all spaces owned by you in the region
     *
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function listSpaces()
    {
        try {
            $this->setSpace(null);
            $spaceList = $this->client->listBuckets();
            $this->setSpace($this->space);
            return $this->objReturn($spaceList->toArray());
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Shorthand for SetSpace - Change your current Space, Region and/or Host.
     *
     * @param $spaceName
     * @param string $region
     * @param string $host
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function changeSpace($spaceName, $region = '', $host = '')
    {
        return $this->setSpace($spaceName, $region, $host);
    }


    /**
     * Changes your current Space, Region and/or Host.
     *
     * @param $spaceName
     * @param string $region
     * @param string $host
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function setSpace($spaceName, $region = '', $host = '')
    {
        if (empty($region)) {
            $region = $this->region;
        }
        if (empty($host)) {
            $host = $this->host;
        }
        if (!empty($spaceName)) {
            $endpoint = 'https://' . $spaceName . '.' . $region . '.' . $host;
            $this->space = $spaceName;
        } else {
            $endpoint = 'https://' . $region . '.' . $host;
            $this->space = '';
        }
        try {
            $this->client = S3Client::factory([
                'region'            => $region,
                'version'           => 'latest',
                'endpoint'          => $endpoint,
                'credentials'       => [
                    'key'    => $this->accessKey,
                    'secret' => $this->secretKey,
                ],
                'bucket_endpoint'   => true,
                'signature_version' => 'v4-unsigned-body'
            ]);
            return $this->objReturn(true);
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Downloads the whole Space to a directory.
     *
     * @param $pathToDirectory
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function downloadSpaceToDirectory($pathToDirectory)
    {
        try {
            $this->client->downloadBucket($pathToDirectory, $this->space);
            return $this->objReturn(true);
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function destroyThisSpace()
    {
        try {
            $objects = $this->ListObjects();
            foreach ($objects as $value) {
                $key = $value['Key'];
                $this->deleteObject($key);
            }
            $this->client->deleteBucket(['Bucket' => $this->space]);
            $this->client->waitUntil('BucketNotExists', ['Bucket' => $this->space]);
            return $this->objReturn(true);
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Lists all objects.
     *
     * @param string $directory
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function listObjects($directory = '')
    {
        try {
            $objects = $this->client->getIterator('ListObjects', [
                'Bucket' => $this->space,
                'Prefix' => $directory,
            ]);
            $objectArray = [];
            foreach ($objects as $object) {
                $objectArray[] = $object;
            }
            return $this->objReturn($objectArray);
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Checks whether an object exists.
     *
     * @param $objectName
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function doesObjectExist($objectName)
    {
        try {
            return $this->objReturn($this->client->doesObjectExist($this->space, $objectName));
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Fetches an object's details.
     *
     * @param string $fileName
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function getObject($fileName = '')
    {
        try {
            $result = $this->client->getObject([
                'Bucket' => $this->space,
                'Key'    => $fileName,
            ]);
            return $this->objReturn($result->toArray());
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Makes an object private, (restricted) access.
     *
     * @param string $filePath
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function makePrivate($filePath = '')
    {
        try {
            return $this->putObjectACL($filePath, ['ACL' => 'private']);
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Makes an object public anyone can access.
     *
     * @param string $filePath
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function makePublic($filePath = '')
    {
        try {
            return $this->putObjectACL($filePath, ['ACL' => 'public-read']);
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * @param string $filePath
     * @param bool $recursive
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function deleteObject($filePath = '', $recursive = false)
    {
        try {
            if ($recursive) {
                $this->client->deleteMatchingObjects(
                    $this->space,
                    $filePath
                );

                return null;
            }

            return $this->objReturn($this->client->deleteObject([
                'Bucket' => $this->space,
                'Key'    => $filePath,
            ])->toArray());

        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * @param $pathToFile
     * @param string $access
     * @param string $saveAs
     * @param string $mimeType
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function uploadFile($pathToFile, $access = 'private', $saveAs = '', $mimeType = 'application/octet-stream')
    {
        if (empty($saveAs)) {
            $saveAs = $pathToFile;
        }
        $access = $access === 'public' ? 'public-read' : $access;

        $isFile = strlen($pathToFile) <= PHP_MAXPATHLEN && is_file($pathToFile);
        if (!$isFile) {
            $file = $pathToFile;
        } else {
            $file = fopen($pathToFile, 'b');
        }
        try {
            $result = $this->client->putObject([
                'Bucket'      => $this->space,
                'Key'         => $saveAs,
                'Body'        => $file,
                'ACL'         => $access,
                'ContentType' => $mimeType
            ]);

            $this->client->waitUntil('ObjectExists', [
                'Bucket' => $this->space,
                'Key'    => $saveAs
            ]);

            return $this->objReturn($result->toArray());
        } catch (Exception $e) {
            $this->handleAWSException($e);
        } finally {
            if (is_resource($file)) {
                fclose($file);
            }
        }
    }

    /**
     * @param $fileName
     * @param bool $destinationPath
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function downloadFile($fileName, $destinationPath = false)
    {
        try {
            if (!$destinationPath) {
                $result = $this->client->getObject([
                    'Bucket' => $this->space,
                    'Key'    => $fileName,
                ]);

                return $result['Body'];
            }

            $result = $this->client->getObject([
                'Bucket' => $this->space,
                'Key'    => $fileName,
                'SaveAs' => $destinationPath
            ]);

            return $this->objReturn($result->toArray());

        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Uploads all contents of a directory.
     *
     * @param $directory
     * @param string $keyPrefix
     * @throws SpacesAPIException
     */
    public function uploadDirectory($directory, $keyPrefix = '')
    {
        try {
            $this->client->uploadDirectory($directory, $this->space, $keyPrefix);
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Lists the CORS policy of the Space.
     *
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function listCORS()
    {
        try {
            $cors = $this->client->getBucketCors([
                'Bucket' => $this->space,
            ]);
            return $this->objReturn($cors->toArray());
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Updates the CORS policy of the Space.
     *
     * @param array $corsRules
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function putCORS($corsRules = [])
    {
        if (empty($corsRules)) {
            $corsRules = [
                'AllowedMethods' => ['GET'],
                'AllowedOrigins' => ['*'],
                'ExposeHeaders'  => ['Access-Control-Allow-Origin'],
            ];
        }
        try {
            $result = $this->client->putBucketCors([
                'Bucket'            => $this->space,
                'CORSConfiguration' => ['CORSRules' => [$corsRules]]
            ]);
            return $this->objReturn($result->toArray());
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Fetches the ACL (Access Control Lists) of the Space.
     *
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function listSpaceACL()
    {
        try {
            $acl = $this->client->getBucketAcl([
                'Bucket' => $this->space,
            ]);
            return $this->objReturn($acl->toArray());
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Updates the ACL (Access Control Lists) of the Space.
     *
     * @param $params
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function putSpaceACL($params)
    {
        try {
            $acl = $this->client->putBucketAcl($params);
            return $this->objReturn($acl->toArray());
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Lists an object's ACL (Access Control Lists).
     *
     * @param $file
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function listObjectACL($file)
    {
        try {
            $result = $this->client->getObjectAcl([
                'Bucket' => $this->space,
                'Key'    => $file,
            ]);
            return $this->objReturn($result->toArray());
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Updates an object's ACL (Access Control Lists).
     *
     * @param $file
     * @param $acl
     * @return array|mixed
     * @throws SpacesAPIException
     */
    public function putObjectACL($file, $acl)
    {
        try {
            $acl = array_merge(['Bucket' => $this->space, 'Key' => $file], $acl);
            $result = $this->client->putObjectAcl($acl);
            return $this->objReturn($result->toArray());
        } catch (Exception $e) {
            $this->handleAWSException($e);
        }
    }


    /**
     * Creates a temporary URL for a file (Mainly for accessing private files).
     *
     * @param string $fileName
     * @param string $validFor
     * @return string
     */
    public function createTemporaryURL($fileName = '', $validFor = '1 hour'): string
    {
        $cmd = $this->client->getCommand('GetObject', [
            'Bucket' => $this->space,
            'Key'    => $validFor
        ]);
        $request = $this->client->createPresignedRequest($cmd, $validFor);

        return (string)$request->getUri();
    }
}
