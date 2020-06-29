<?php

namespace Ibnab\Bundle\CustomRelatedBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class CustomRelatedBundle implements Migration
{

    const CUSTOM_PRODUCTS_TABLE_NAME = 'oro_product_cr_products';

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createCustomRelatedProductsTable($schema);

    }
    /**
     * @param Schema $schema
     */
    private function createCustomRelatedProductsTable(Schema $schema)
    {
        $table = $schema->createTable(self::CUSTOM_PRODUCTS_TABLE_NAME);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', ['notnull' => true]);
        $table->addColumn('related_item_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['product_id'], 'idx_oro_product_customrelatedt_products_product_id', []);
        $table->addIndex(['related_item_id'], 'idx_oro_product_customrelatedproducts_related_item_id', []);
        $table->addUniqueIndex(['product_id', 'related_item_id'], 'idx_oro_product_customrelated_products_unique');

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_product'),
            ['related_item_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

}
