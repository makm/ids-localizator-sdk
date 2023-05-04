<?php

namespace Ids\Localizator\Client;

use GuzzleHttp\ClientInterface;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

class ClientBuilder
{
    public const LOCALIZATOR_URL = 'https://localizator.prod.idynsys.org';
    private string $localizatorUrl;
    private ClientInterface $guzzleClient;
    private SerializerInterface $serializer;

    public function __construct(?string $localizatorUrl= null)
    {
        $this->localizatorUrl = $localizatorUrl ?: self::LOCALIZATOR_URL;
        $this->configureDefaultClient();
        $this->configureDefaultSerializer();
    }

    public static function create(?string $localizatorUrl = self::LOCALIZATOR_URL): self
    {
        return new static($localizatorUrl);
    }

    /**
     * @param  ClientInterface  $guzzleClient
     * @return ClientBuilder
     */
    public function setGuzzleClient(ClientInterface $guzzleClient): ClientBuilder
    {
        $this->guzzleClient = $guzzleClient;

        return $this;
    }

    /**
     * @param  SerializerInterface  $serializer
     * @return ClientBuilder
     */
    public function setSerializer(SerializerInterface $serializer): ClientBuilder
    {
        $this->serializer = $serializer;

        return $this;
    }

    public function configureDefaultClient(): void
    {
        $this->guzzleClient = new \GuzzleHttp\Client(
            [
                'base_uri' => $this->localizatorUrl,
                'Content-Type' => 'application/json',
            ]
        );
    }

    public function configureDefaultSerializer(): void
    {
        $this->serializer = SerializerBuilder::create()
//            ->setPropertyNamingStrategy(new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy()))
            ->build();
    }

    public function build(): Client
    {
        return new Client($this->guzzleClient, $this->serializer);
    }
}
