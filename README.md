# IDS api client sdk

Install
```shell script
composer req idynsys/localizator
```



Как использовать:

```
$translator = TranslatorFactory::create($applicationId, 'rus')->build();

// получить переводы UI
$result = $translator->translateUi('exceptions', 'LAP-00005', $productId);

// аналогично получаются переводы из общего каталога
$result = $translator->translate('Organizations', 'title', $productId);

//если перевод - шаблонная строка, то можно разобрать 
$result->renderWith('rus'); 
//или
$result('rus');

// при переводе "Язык не найден или не установлен/активирован (%s)"
// %s заменится на rus

```

для удобства, можно указать $productId по умолчнию и не указывать его далее в целом

```
$translator->setDefaultProductId(-1)
//или
$translator = TranslatorFactory::create($applicationId, 'rus')
                ->setDefaultProductId(-1)
                ->build();
                
$result = $translator->translate('Organizations', 'title');                
```


Разное

```        
//Сбросить кэш
$translator->reset();       

//выключить прогрев кеша автоматически
$translator->setWarmCacheIfEmpty(false)


//использовать собственную реализацию кешера Psr\Cache\CacheItemPoolInterface

$translator = TranslatorFactory::create($applicationId, 'rus')
                ->setCache(new RedisAdapter(new \Redis()))
                ->build();

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
