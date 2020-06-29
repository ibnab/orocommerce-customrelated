<?php

namespace Ibnab\Bundle\CustomRelatedBundle\RelatedItem;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ProductBundle\Entity\Product;
use Ibnab\Bundle\CustomRelatedBundle\Entity\CustomRelatedProduct;
use Oro\Bundle\ProductBundle\RelatedItem\FinderStrategyInterface;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;

/**
 * Provides methods to get ids of instances of related products.
 */
class FinderDatabaseStrategy implements FinderStrategyInterface
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;



    /**
     * @param DoctrineHelper                    $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     * If parameters `bidirectional` and `limit` are not passed - default values from configuration will be used
     */
    public function findIds(Product $product, $bidirectional = false, $limit = null,$order='position')
    {
        return $this->getCustomRelatedProductsRepository()
            ->findRelatedIds(
                $product->getId(),
                $bidirectional,
                $limit,
                $order
            );
    }
    /**
     * {@inheritdoc}
     * If parameters `bidirectional` and `limit` are not passed - default values from configuration will be used
     */
    public function findIdsByIds($ids, $bidirectional = false, $limit = null,$order='position')
    {
        return $this->getCustomRelatedProductsRepository()
            ->findRelatedIds(
                $ids,
                $bidirectional,
                $limit,
                $order
            );
    }
    /**
     * {@inheritdoc}
     * If parameters `bidirectional` and `limit` are not passed - default values from configuration will be used
     */
    public function findLinks(Product $product, $bidirectional = false, $limit = null,$order='position')
    {
        return $this->getCustomRelatedProductsRepository()
            ->findRelatedLinks(
                $product->getId(),
                $bidirectional,
                $limit,
                $order
            );
    }
    /**
     * @return CustomRelatedProductRepository|EntityRepository
     */
    private function getCustomRelatedProductsRepository()
    {
        return $this->doctrineHelper->getEntityRepository(CustomRelatedProduct::class);
    }
    public function findLineItemOrderIds($product)
    {
        
        return $this->getLineItemsRepository()
            ->findBy(['product' => $product]);
    }
    public function findLineItemByOrderIds($orderIds)
    {
        return $this->getLineItemsRepository()
            ->findBy(['order' => $orderIds]);
    }
    public function findProduct($id)
    {
        return $this->getProductRepository()
            ->find($id);
    }
    public function findbyId($ids)
    {
        return $this->getProductRepository()
            ->findById($ids);
    }
    /**
     * @return ProductRepository|EntityRepository
     */
    private function getProductRepository()
    {
        return $this->doctrineHelper->getEntityRepository(Product::class);
    }
    /**
     * @return ProductRepository|EntityRepository
     */
    private function getLineItemsRepository()
    {
        return $this->doctrineHelper->getEntityRepository(OrderLineItem::class);
    }
    public function fullEmpty() {
      $this->getCustomRelatedProductsRepository()->fullEmpty();
    }    
}
