<?php

namespace XtractPDF\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Mongo;
use Doctrine\Common\ClassLoader as MongoClassLoader,
    Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\MongoDB\Connection as MongoConnection,
    Doctrine\ODM\MongoDB\Configuration as MongoConfiguration,
    Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

/**
 * Doctrine Mongo Service Provider
 * Uses Doctrine Mongo ODM with Annotation Driver
 */
class DoctrineMongoServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {   
        $params = (isset($app['mongo.params']))
            ? $app['mongo.params']
            : array();

        $app['mongo.conn'] = $app->share(function($app) use ($params) {
            $conn = (isset($params['connstring']) && $params['connstring'])
                ? new MongoConnection(new Mongo($params['connstring']))
                : new MongoConnection();
        });

        $app['mongo'] = $app->share(function($app) use ($params) {

            AnnotationDriver::registerAnnotationClasses();

            //Params and their defaults
            $proxyDir = isset($params['proxy_dir'])
                ? $params['proxy_dir'] 
                : sys_get_temp_dir();

            $hydratorDir = isset($params['hydrator_dir'])
                ? $params['hydrator_dir'] 
                : sys_get_temp_dir();

            $dbName = isset($params['dbname'])
                ? $params['dbname'] 
                : 'xtractpdf';

            $docPath = isset($app['mongo.documents_path'])
                ? $app['mongo.documents_path']
                : __DIR__ . '/../Model';

            //Config
            $config = new MongoConfiguration();
            $config->setProxyDir($proxyDir);
            $config->setProxyNamespace('Proxies');
            $config->setHydratorDir($hydratorDir);
            $config->setHydratorNamespace('Hydrators');
            $config->setMetadataDriverImpl(AnnotationDriver::create($docPath));
            $config->setDefaultDB($dbName);
    
            //Set it up
            return DocumentManager::create($app['mongo.conn'], $config);
        });
    }

    // --------------------------------------------------------------

    public function boot(Application $app)
    {
        //Nothing, but its required by ServiceProviderInterface
    }
}

/* EOF: DoctrineMongoServiceProvider.php */