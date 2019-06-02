<?php

use //Doctrine\DBAL\Logging\EchoSQLLogger,
Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\ClassLoader;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\EntityGenerator;

/**
 * CodeIgniter Smarty Class
 *
 * initializes basic doctrine settings and act as doctrine object
 *
 * @final   Doctrine
 * @category    Libraries
 * @author  Md. Ali Ahsan Rana
 * @link    http://codesamplez.com/
 */
class Doctrine
{

    /**
     * @var EntityManager $em
     */
    public $em = null;

    /**
     * constructor
     */
    public function __construct()
    {
        // load database configuration from CodeIgniter
        require APPPATH . 'config/database.php';

        // Set up class loading. You could use different autoloaders, provided by your favorite framework,
        // if you want to.
        require_once APPPATH . './../vendor/autoload.php';

        $doctrineClassLoader = new ClassLoader('Doctrine', APPPATH . 'third_party');
        $doctrineClassLoader->register();
        $entitiesClassLoader = new ClassLoader('models', rtrim(APPPATH, "/"));
        $entitiesClassLoader->register();
        $proxiesClassLoader = new ClassLoader('proxies', APPPATH . 'models');
        $proxiesClassLoader->register();

        // Set up caches
        $config = new Configuration;
        $cache = new ArrayCache;
        $config->setMetadataCacheImpl($cache);
        $driverImpl = $config->newDefaultAnnotationDriver(array(APPPATH . 'models/Entidades'));
        $config->setMetadataDriverImpl($driverImpl);
        $config->setQueryCacheImpl($cache);

        // Proxy configuration
        $config->setProxyDir(APPPATH . 'models/proxies');
        $config->setProxyNamespace('Proxies');

        // Set up logger
        //$logger = new EchoSQLLogger;
        //$config->setSQLLogger($logger);

        $config->setAutoGenerateProxyClasses(true);
        // Database connection information
        $connectionOptions = array(
            'driver' => 'pdo_mysql',
            'user' => $db['default']['username'],
            'password' => $db['default']['password'],
            'host' => $db['default']['hostname'],
            'dbname' => $db['default']['database'],
        );

        // Create EntityManager
        $this->em = EntityManager::create($connectionOptions, $config);
    }

    //------------------------------------------

    /**
     * generate entity objects automatically from mysql db tables
     * @return none
     */
    public function generate_classes()
    {

        $this->em->getConfiguration()
            ->setMetadataDriverImpl(
                new DatabaseDriver(
                    $this->em->getConnection()->getSchemaManager()
                )
            );

        $cmf = new DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($this->em);
        $metadata = $cmf->getAllMetadata();
        $generator = new EntityGenerator();

        $generator->setUpdateEntityIfExists(true);
        $generator->setGenerateStubMethods(true);
        $generator->setGenerateAnnotations(true);
        $generator->generate($metadata, APPPATH . "models/Entidades");
    }

    //------------------------------------------
}
