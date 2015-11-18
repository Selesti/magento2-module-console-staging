<?php
namespace Shockwavemk\Staging\Console\Configuration;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use \Magento\Framework\App\ObjectManagerFactory;
use \Magento\Store\Model\StoreManager;

/**
 * Class GetEnvironmentCommand returns the current environment name
 *
 * @package Shockwavemk\Staging
 */
class SetConfigurationCommand extends Command
{
    const COMMAND_NAME = 'staging:config:set';
    const DB_CONFIG_FILE_PATH_ARGUMENT_NAME = 'db-config-file-path';

    protected $configResource;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        $this->configResource = $this->getConfigResource($objectManagerFactory);
        parent::__construct();
    }

    private function getConfigResource(ObjectManagerFactory $objectManagerFactory)
    {
        try {
            $params = $_SERVER;
            $params[StoreManager::PARAM_RUN_CODE] = 'admin';
            $params[StoreManager::PARAM_RUN_TYPE] = 'store';
            $objectManager = $objectManagerFactory->create($params);

            /** @var \Magento\Config\Model\Resource\Config $test */
            return $objectManager->get('Magento\Config\Model\Resource\Config');
        }
        catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Set the defined config values in database')
            ->setHelp('The <info>' . self::COMMAND_NAME . '</info> returns the current environment name.')
            ->setDefinition(
                array(
                    new InputArgument(
                        self::DB_CONFIG_FILE_PATH_ARGUMENT_NAME,
                        InputArgument::REQUIRED,
                        'The path of the config file (e.g. ./config/default_db.php)'
                    ),
                )
            )
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument(self::DB_CONFIG_FILE_PATH_ARGUMENT_NAME);
        if (empty($path)) {
            $output->writeln('The supplied config file path name cannot be null or empty.');

            return;
        }

        $dbConfig = $this->getDbConfigArray($path);
        if(empty($dbConfig))
        {
            throw new \InvalidArgumentException('The config file can not be empty.');
        }

        foreach($dbConfig as $scope => $scopeConfig) {
            foreach($scopeConfig as $scopeId => $scopeIdConfig) {
                foreach($scopeIdConfig as $path => $value) {
                    $this->configResource->saveConfig(
                        $path,
                        $value,
                        $scope,
                        $scopeId
                    );
                }
            }
        }
    }

    protected function getDbConfigArray($path)
    {
        if(is_file($path))
        {
            return include $path;
        }

        return array();
    }
}
