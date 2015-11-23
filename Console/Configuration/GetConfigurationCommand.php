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
class GetConfigurationCommand extends Command
{
    const COMMAND_NAME = 'staging:config:get';
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


    /**
     * @param ObjectManagerFactory $objectManagerFactory
     * @return \Shockwavemk\Staging\Console\Model\Resource\Config|null
     */
    private function getConfigResource(ObjectManagerFactory $objectManagerFactory)
    {
        try {
            $params = $_SERVER;
            $params[StoreManager::PARAM_RUN_CODE] = 'admin';
            $params[StoreManager::PARAM_RUN_TYPE] = 'store';
            $objectManager = $objectManagerFactory->create($params);

            /** @var \Shockwavemk\Staging\Console\Model\Resource\Config */
            return $objectManager->get('Shockwavemk\Staging\Console\Model\Resource\Config');
        }
        catch (\Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Get the defined config values in database as save-able array')
            ->setHelp('The <info>' . self::COMMAND_NAME . '</info> returns the current environment name.')
            ->setDefinition(
                array(
                    new InputArgument(
                        self::DB_CONFIG_FILE_PATH_ARGUMENT_NAME,
                        InputArgument::REQUIRED,
                        'The path of the config file (e.g. ./config/backup_db.php)'
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
            return 1;
        }

        $result = $this->configResource->fetchAll();
        $exportConfig = array();

        foreach($result as $dbConfig) {
            $exportConfig[$dbConfig['scope']][$dbConfig['scope_id']][$dbConfig['path']] = $dbConfig['value'];
        }

        $val = var_export($exportConfig, true);

        if (!is_writable(dirname($path))) {
            $output->writeln('The supplied config file path is not writeable.');
            return 1;
        }

        $handle = fopen($path, 'w');
        fwrite($handle, "<?php return $val;");

        return 0;
    }
}

// SELECT concat("'", path, "' => '", value, "',") FROM magento.core_config_data WHERE path LIKE '%smtp%'