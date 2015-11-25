<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Shockwavemk\Staging\Console\Model\Resource;

/**
 * Core Resource Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Config extends \Magento\Config\Model\ResourceModel\Config implements \Magento\Framework\App\Config\ConfigResource\ConfigInterface
{
    /**
     * Delete config value
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return $this
     */
    public function getConfig($path, $scope, $scopeId)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            $this->getMainTable()
        )->where(
            'path = ?',
            $path
        )->where(
            'scope = ?',
            $scope
        )->where(
            'scope_id = ?',
            $scopeId
        );

        return $adapter->fetchRow($select);
    }

    public function fetchAll()
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            $this->getMainTable()
        );

        return $adapter->fetchAll($select);
    }


}
