<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Setup;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory, EavSetupFactory $eavSetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if ($context->getVersion()
            && version_compare($context->getVersion(), '2.0.1') < 0
        ) {
            $select = $setup->getConnection()->select()
                ->from(
                    $setup->getTable('catalog_product_entity_group_price'),
                    [
                        'entity_id',
                        'all_groups',
                        'customer_group_id',
                        new \Zend_Db_Expr('1'),
                        'value',
                        'website_id'
                    ]
                );
            $select = $setup->getConnection()->insertFromSelect(
                $select,
                $setup->getTable('catalog_product_entity_tier_price'),
                [
                    'entity_id',
                    'all_groups',
                    'customer_group_id',
                    'qty',
                    'value',
                    'website_id'
                ]
            );
            $setup->getConnection()->query($select);

            $categorySetupManager = $this->categorySetupFactory->create();
            $categorySetupManager->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'group_price');
        }

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            // set new resource model paths
            /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $categorySetup->updateEntityType(
                \Magento\Catalog\Model\Category::ENTITY,
                'entity_model',
                'Magento\Catalog\Model\ResourceModel\Category'
            );
            $categorySetup->updateEntityType(
                \Magento\Catalog\Model\Category::ENTITY,
                'attribute_model',
                'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
            );
            $categorySetup->updateEntityType(
                \Magento\Catalog\Model\Category::ENTITY,
                'entity_attribute_collection',
                'Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection'
            );
            $categorySetup->updateAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'custom_design_from',
                'attribute_model',
                'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
            );
            $categorySetup->updateEntityType(
                \Magento\Catalog\Model\Product::ENTITY,
                'entity_model',
                'Magento\Catalog\Model\ResourceModel\Product'
            );
            $categorySetup->updateEntityType(
                \Magento\Catalog\Model\Product::ENTITY,
                'attribute_model',
                'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
            );
            $categorySetup->updateEntityType(
                \Magento\Catalog\Model\Product::ENTITY,
                'entity_attribute_collection',
                'Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection'
            );
        }

        if (version_compare($context->getVersion(), '2.0.3') < 0) {
            /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $categorySetup->updateAttribute(3, 51, 'default_value', 1);
        }

        if (version_compare($context->getVersion(), '2.0.4') < 0) {
            /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $categorySetup->updateAttribute(
                'catalog_product',
                'media_gallery',
                'backend_type',
                'static'
            );
            $categorySetup->updateAttribute(
                'catalog_product',
                'media_gallery',
                'backend_model'
            );
        }

        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

            //Product Details tab
            $categorySetup->updateAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'status',
                'frontend_label',
                'Enable Product',
                5
            );
            $categorySetup->updateAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'name',
                'frontend_label',
                'Product Name'
            );
            $categorySetup->addAttributeToGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Product Details',
                'visibility',
                80
            );
            $categorySetup->addAttributeToGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Product Details',
                'news_from_date',
                90
            );
            $categorySetup->addAttributeToGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Product Details',
                'news_to_date',
                100
            );
            $categorySetup->addAttributeToGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Product Details',
                'country_of_manufacture',
                110
            );

            //Content tab
            $categorySetup->addAttributeGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Content',
                15
            );
            $categorySetup->updateAttributeGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Content',
                'tab_group_code',
                'basic'
            );
            $categorySetup->addAttributeToGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Content',
                'description'
            );
            $categorySetup->addAttributeToGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Content',
                'short_description',
                100
            );

            //Images tab
            $groupId = (int)$categorySetup->getAttributeGroupByCode(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'image-management',
                'attribute_group_id'
            );
            $categorySetup->addAttributeToGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                $groupId,
                'image',
                1
            );
            $categorySetup->updateAttributeGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                $groupId,
                'attribute_group_name',
                'Images'
            );
            $categorySetup->updateAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'image',
                'frontend_label',
                'Base'
            );
            $categorySetup->updateAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'small_image',
                'frontend_label',
                'Small'
            );

            //Design tab
            $categorySetup->updateAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'page_layout',
                'frontend_label',
                'Layout'
            );
            $categorySetup->updateAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'custom_layout_update',
                'frontend_label',
                'Layout Update XML',
                10
            );

            //Schedule Design Update tab
            $categorySetup->addAttributeGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Schedule Design Update',
                55
            );
            $categorySetup->updateAttributeGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Schedule Design Update',
                'tab_group_code',
                'advanced'
            );
            $categorySetup->addAttributeToGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Schedule Design Update',
                'custom_design_from',
                20
            );
            $categorySetup->addAttributeToGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Schedule Design Update',
                'custom_design_to',
                30
            );
            $categorySetup->updateAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'custom_design',
                'frontend_label',
                'New Theme',
                40
            );
            $categorySetup->addAttributeToGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Schedule Design Update',
                'custom_design'
            );
            $categorySetup->addAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'custom_layout',
                [
                    'type' => 'varchar',
                    'label' => 'New Layout',
                    'input' => 'select',
                    'source' => 'Magento\Catalog\Model\Product\Attribute\Source\Layout',
                    'required' => false,
                    'sort_order' => 50,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Schedule Design Update',
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false
                ]
            );

            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $field = 'weight';
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
            );
            if ($key = array_search('virtual', $applyTo)) {
                unset($applyTo[$key]);
                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
        }

        $setup->endSetup();
    }
}
