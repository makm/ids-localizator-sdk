# IDS api client sdk

Install
```shell script
composer req idynsys/localizator
```



Как использовать:

```
// $productId не является обязательным для установки в setDefaultProductId. 
// Тогда необходимо указывать его при получении перевода:  
// $translator->translate('Organizations', 'title', 5);

        $applicationId = 1;
        $productId  = 5; 

        $translator = TranslatorFactory::create($applicationId, 'rus')
            ->setDefaultProductId($productId)
            ->build();
            
        $translation = $translator->translate('Organizations', 'title');            
```



Некоторые команды для разработки

```
docker-compose run --rm php-cli composer --version
docker-compose run --rm php-cli composer install
```

Запустить тесты
```
docker-compose run --rm php-cli /composer-package/vendor/phpunit/phpunit/phpunit --no-configuration /composer-package/tests
```