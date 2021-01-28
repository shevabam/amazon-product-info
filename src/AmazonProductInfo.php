<?php

namespace AmazonProductInfo;

use Amazon\ProductAdvertisingAPI\v1\ApiException;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException;
use Amazon\ProductAdvertisingAPI\v1\Configuration;

class AmazonProductInfo
{
    /**
     * API connection data
     * @var array
     */
    private $params = [
        'access_key'    => null,
        'secret_key'    => null,
        'partner_tag'   => null,
        'lang'          => 'us',
    ];

    /**
     * Amazon API config
     * @var object
     */
    private $config;

    /**
     * Amazon API instance
     * @var object
     */
    private $instance;

    /*
     * PAAPI host and region to which you want to send request
     * For more details refer:
     * https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region
     */
    private $regions = [
        'au' => ['host' => 'webservices.amazon.com.au', 'region' => 'us-west-2'],
        'br' => ['host' => 'webservices.amazon.com.br', 'region' => 'us-east-1'],
        'ca' => ['host' => 'webservices.amazon.ca', 'region' => 'us-east-1'],
        'fr' => ['host' => 'webservices.amazon.fr', 'region' => 'eu-west-1'],
        'de' => ['host' => 'webservices.amazon.de', 'region' => 'eu-west-1'],
        'in' => ['host' => 'webservices.amazon.in', 'region' => 'eu-west-1'],
        'it' => ['host' => 'webservices.amazon.it', 'region' => 'eu-west-1'],
        'jp' => ['host' => 'webservices.amazon.co.jp', 'region' => 'us-west-2'],
        'mx' => ['host' => 'webservices.amazon.com.mx', 'region' => 'us-east-1'],
        'nl' => ['host' => 'webservices.amazon.nl', 'region' => 'eu-west-1'],
        'sg' => ['host' => 'webservices.amazon.sg', 'region' => 'us-west-2'],
        'sa' => ['host' => 'webservices.amazon.sa', 'region' => 'eu-west-1'],
        'es' => ['host' => 'webservices.amazon.es', 'region' => 'eu-west-1'],
        'se' => ['host' => 'webservices.amazon.se', 'region' => 'eu-west-1'],
        'tr' => ['host' => 'webservices.amazon.com.tr', 'region' => 'eu-west-1'],
        'ae' => ['host' => 'webservices.amazon.ae', 'region' => 'eu-west-1'],
        'uk' => ['host' => 'webservices.amazon.co.uk', 'region' => 'eu-west-1'],
        'us' => ['host' => 'webservices.amazon.com', 'region' => 'us-east-1'],
    ];


    /**
     * Constructor
     * 
     * @param array $params Contains access_key, secret_key, partner_tag, lang (see global var $params)
     */
    public function __construct(array $params)
    {
        $this->params = $params;

        $this->setConfig($params);

        $this->instance = new DefaultApi(new \GuzzleHttp\Client(['verify' => false]), $this->config);
    }

    /**
     * Sets config
     *
     * @param array $params Parameters (see global var $params)
     */
    private function setConfig($params)
    {
        $this->config = new Configuration();

        $this->config->setAccessKey($params['access_key']);
        $this->config->setSecretKey($params['secret_key']);

        $this->config->setHost($this->regions[$this->params['lang']]['host']);
        $this->config->setRegion($this->regions[$this->params['lang']]['region']);
    }


    /**
     * Search item(s) by ASIN number
     *
     * @param array $itemIds Array of ASIN number
     *
     * @return array Products information
     */
    public function searchByAsin(array $itemIds)
    {
        $results = ['error' => null, 'datas' => []];

        if (count($itemIds) == 0 || !is_array($itemIds))
        {
            $results['error'] = 'No item found';
        }
        else
        {
            // https://webservices.amazon.com/paapi5/documentation/get-items.html#resources-parameter
            $resources = [
                GetItemsResource::ITEM_INFOTITLE,
                GetItemsResource::OFFERSLISTINGSPRICE,
                GetItemsResource::IMAGESPRIMARYSMALL,
                GetItemsResource::IMAGESPRIMARYMEDIUM,
                GetItemsResource::IMAGESPRIMARYLARGE,
            ];

            // Forming the request
            $getItemsRequest = new GetItemsRequest();
            $getItemsRequest->setItemIds($itemIds);
            $getItemsRequest->setPartnerTag($this->params['partner_tag']);
            $getItemsRequest->setPartnerType(PartnerType::ASSOCIATES);
            $getItemsRequest->setResources($resources);

            // Validating request
            $invalidPropertyList = $getItemsRequest->listInvalidProperties();
            $length = count($invalidPropertyList);
            if ($length > 0)
            {
                $results['error'] = 'Error forming the request';
            }
            else
            {
                try
                {
                    $getItemsResponse = $this->instance->getItems($getItemsRequest);

                    if ($getItemsResponse->getItemsResult() !== null)
                    {
                        if ($getItemsResponse->getItemsResult()->getItems() !== null)
                        {
                            $responseList = $this->parseResponse($getItemsResponse->getItemsResult()->getItems());

                            foreach ($itemIds as $itemId)
                            {
                                $item = $responseList[$itemId];

                                $itemDatas = [];

                                if ($item !== null)
                                {
                                    $itemDatas['title'] = $this->getItemTitle($item);
                                    $itemDatas['url'] = $this->getUrl($item);
                                    $itemDatas['images'] = $this->getImages($item);
                                    $itemDatas['price'] = $this->getPrice($item);
                                }
                                else
                                {
                                    $results['error'] = "Item not found, check errors";
                                }

                                $results['datas'][$itemId] = $itemDatas;
                            }
                        }
                    }

                    if ($getItemsResponse->getErrors() !== null)
                    {
                        $results['error'] = $getItemsResponse->getErrors()[0]->getCode().' - '.$getItemsResponse->getErrors()[0]->getMessage();
                    }
                } catch (Exception $exception)
                {
                    $results['error'] = $exception->getMessage();
                }
            }
        }


        return $results;
    }


    /**
     * Get item title
     *
     * @param object $item \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item
     *
     * @return string Product title
     */
    private function getItemTitle($item)
    {
        $result = null;

        if ($item->getItemInfo() !== null && $item->getItemInfo()->getTitle() !== null && $item->getItemInfo()->getTitle()->getDisplayValue() !== null)
        {
            $result = $item->getItemInfo()->getTitle()->getDisplayValue();
        }

        return $result;
    }


    /**
     * Get item URL
     *
     * @param object $item \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item
     *
     * @return string Product URL
     */
    private function getUrl($item)
    {
        $result = null;

        if ($item->getDetailPageURL() !== null)
        {
            $result = $item->getDetailPageURL();
        }

        return $result;
    }


    /**
     * Get item images
     *
     * @param object $item \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item
     *
     * @return array Product primary images
     */
    private function getImages($item)
    {
        $sizes = ['small', 'medium', 'large'];
        $result = null;

        if ($item->getImages() !== null && $item->getImages()->getPrimary() !== null)
        {
            foreach ($sizes as $size)
            {
                $method = 'get'.ucfirst($size);

                $result['primary'][$size] = [
                    'url'    => $item->getImages()->getPrimary()->$method()->getURL(),
                    'width'  => $item->getImages()->getPrimary()->$method()->getWidth(),
                    'height' => $item->getImages()->getPrimary()->$method()->getHeight(),
                ];
            }
        }
        

        return $result;
    }


    /**
     * Get item price
     *
     * @param object $item \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item
     *
     * @return string Product price
     */
    private function getPrice($item)
    {
        $result = null;

        if ($item->getOffers() !== null && $item->getOffers()->getListings() !== null && $item->getOffers()->getListings()[0]->getPrice() !== null && $item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount() !== null)
        {
            $result = $item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount();
        }

        return $result;
    }



    /**
     * Returns the array of items mapped to ASIN
     *
     * @param array $items Items value.
     * @return array of \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item mapped to ASIN.
     */
    public function parseResponse($items)
    {
        $mappedResponse = [];
        foreach ($items as $item) {
            $mappedResponse[$item->getASIN()] = $item;
        }
        return $mappedResponse;
    }

}
