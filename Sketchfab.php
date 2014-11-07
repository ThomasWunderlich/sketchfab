<?php


/**
 * Class Sketchfab
 * Sketchfab Api Library
 * Requires Guzzle 3.8.*
 *
 * @package Sketchfab
 * @author Thomas Wunderlich (twunde)
 * @copyright ©2014
 */
class Sketchfab {


    const apiEndpoint = 'https://api.sketchfab.com/v2/models';
    const oEmbedEndpoint = 'https://sketchfab.com/oembed';
    const oEmbedBaseUrl = 'https://sketchfab.com/models';

    protected $apiToken;
    protected $client;

    /**
     * Create and setup the internal client
     * @param $apiToken
     */
    public function __construct($apiToken)
    {
        $this->apiToken = $apiToken;
        $this->client = new \Guzzle\Http\Client();
    }


    /**
     * Upload a model to Sketchfab
     * @param $data
     * @param $file
     * @return array|bool|float|int|string
     * @throws \Exception
     */
    public function upload($data, $file)
    {
        $defaults = array(
            'token' => $this->apiToken,
            'name' => '',
            'description' => '', //optional
            'tags' => '', //optional
            //'private' => '', //requires pro account
            //'password' => '' //requires pro account
        );

        //merge and overwrite defaults where applicable
        $data = array_merge($defaults, $data);

        //set the file
        $data['modelFile'] = '@'.$file;

        $request = $this->client->post(self::apiEndpoint,null,
            $data
            );
        $response = $request->send();

        if($response->isError()) {
            throw new \Exception('Model was not created: '. $response->getBody(true));
        }

        return $response->json();
    }

    /**
     *  Poll for file processing status
     * @param $modelId
     * @return array|bool|float|int|string
     * @throws \Exception
     */
    public function pollProcessingStatus($modelId)
    {
        $request = $this->client->get(self::apiEndpoint . '/' .$modelId . '/status?token='.$this->apiToken);
        $response = $request->send();

        if($response->isError()) {
            throw new \Exception('Could not poll Model: '.$response->getBody());
        }

        return $response->json();

    }


    /**
     * Get the oEmbed data for the SketchFab model
     * @param $modelId
     * @return array|bool|float|int|string
     * @throws \Exception
     */
    public function getEmbedData($modelId)
    {
        $request = $this->client->get(self::oEmbedEndpoint.'?url=https://sketchfab.com/models/'.$modelId);
        $response = $request->send();
        if($response->isError()) {
            throw new \Exception('Could not get the Model embed info: '.$response->getBody());
        }
        return $response->json();
    }

    /**
     * @param $modelId
     * @return \Guzzle\Http\Message\Response
     * @throws \Exception
     */
    public function getEmbedThumbnail($modelId)
    {
        $result = $this->getEmbedData($modelId);

        $response = $this->client->get($result['thumbnail_url'], null, tmpfile())->send();
        return $response;
    }



}
