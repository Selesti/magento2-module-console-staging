<?php
namespace Shockwavemk\Staging\Console\Configuration;

use Symfony\Component\Console\Command\Command;
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
    const COMMAND_NAME = 'config:set';

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        $params = $_SERVER;
        $params[StoreManager::PARAM_RUN_CODE] = 'admin';
        $params[StoreManager::PARAM_RUN_TYPE] = 'store';
        $objectManager = $objectManagerFactory->create($params);

        /** @var \Magento\Config\Model\Resource\Config $test */
        $test = $objectManager->get('Magento\Config\Model\Resource\Config');

        $path = 'web/unsecure/base_url';
        $value = 'http://example.url';
        $scope = 'default';
        $scopeId = '0';

        $test->saveConfig(
            $path,
            $value,
            $scope,
            $scopeId
        );
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Set the defined config values')
            ->setHelp('The <info>' . self::COMMAND_NAME . '</info> returns the current environment name.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
