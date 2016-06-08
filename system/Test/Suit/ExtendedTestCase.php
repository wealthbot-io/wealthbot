<?php

namespace Test\Suit;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

$loader = require_once __DIR__.'/../../../app/autoload.php';

use Database\WealthbotSqliteConnection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class ExtendedTestCase extends WebTestCase
{
    protected static function initialize()
    {
        self::createClient();

        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        //static::createDatabase($application);
    }

    protected static function getPhpUnitXmlDir()
    {
        $dir = __DIR__ . '/../../../app';
        if ($dir === null &&
            (is_file(getcwd().DIRECTORY_SEPARATOR.'phpunit.xml') ||
                is_file(getcwd().DIRECTORY_SEPARATOR.'phpunit.xml.dist'))) {
            $dir = getcwd();
        }

        // Can't continue
        if ($dir === null) {
            throw new \RuntimeException('Unable to guess the Kernel directory.');
        }

        if (!is_dir($dir)) {
            $dir = dirname($dir);
        }

        return $dir;
    }


    private static function createDatabase(Application $application)
    {
        $output = new ConsoleOutput();

        self::executeCommand($application, 'doctrine:database:drop', array('--force' => true));

        $connection = $application->getKernel()->getContainer()->get('doctrine')->getConnection();
        if ($connection->isConnected()) {
            $connection->close();
        }

        self::executeCommand($application, 'doctrine:database:create');

        $output->write('Drop database...');
        $dropDatabase = self::executeCommand($application, 'doctrine:schema:drop', array('--force' => true));
        if (0 === $dropDatabase) {
            $output->writeln(' Ok.');
        } else {
            $output->writeln('Error: ' . $dropDatabase . '.');
        }

        $output->write('Create database...');
        $createDatabase = self::executeCommand($application, 'doctrine:schema:create');
        if (0 === $createDatabase) {
            $output->writeln(' Ok.');
        } else {
            $output->writeln('Error: ' . $createDatabase . '.');
        }

        $loadFixturesOptions = array('-n' => true);
        $fixtures = static::getFixtures();
        if (count($fixtures)) {
            $loadFixturesOptions['--fixtures'] = $fixtures;
        }

        $output->write('Load fixtures...');
        $loadFixtures = self::executeCommand($application, 'wealthbot:fixtures:load', $loadFixturesOptions);
        if (0 === $loadFixtures) {
            $output->writeln(' Ok.');
        } else {
            $output->writeln('Error: ' . $loadFixtures . '.');
        }
    }

    private static function executeCommand(Application $application, $command, array $options = array())
    {
        $options['--env'] = 'test';
        $options['--quiet'] = null;
        $options = array_merge($options, array('command' => $command));

        return $application->run(new ArrayInput($options));
    }

    public static function setUpBeforeClass()
    {
        // \Config::$PROD_MODE = \Config::ENV_TEST;
        // WealthbotSqliteConnection::getInstance()->execute("use " . \Config::$SQLITE_DATABASE_TEST);

        static::initialize();
    }

    public static function tearDownAfterClass()
    {
        // \Config::$PROD_MODE = \Config::ENV_PROD;
        // WealthbotSqliteConnection::getInstance()->execute("use " . \Config::$SQLITE_DATABASE_TEST);
    }

    /**
     * Get fixtures
     *
     * @return array
     */
    protected static function getFixtures()
    {
        return array();
    }
} 