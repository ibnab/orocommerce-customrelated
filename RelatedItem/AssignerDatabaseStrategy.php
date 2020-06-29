<?php
namespace Ibnab\Bundle\CustomRelatedBundle\RelatedItem;
use Ibnab\Bundle\CustomRelatedBundle\Entity\CustomRelatedProduct;
use Oro\Bundle\ProductBundle\RelatedItem\AssignerStrategyInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class AssignerDatabaseStrategy implements AssignerStrategyInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper                    $doctrineHelper

     *      * @param AbstractRelatedItemConfigProvider $configProvider     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;

    }
   /**
     * {@inheritdoc}
     */
    public function addRelations(Product $productFrom, array $productsTo)
    {
        if (count($productsTo) === 0) {
            return;
        }
        $productsTo = $this->validateRelations($productFrom, $productsTo);
        if (count($productsTo) === 0) {
            return;
        }
        foreach ($productsTo as $productTo) {
            $this->addRelation($productFrom, $productTo);
        }
        $this->getEntityManager()->flush();
    }
    /**
     * {@inheritdoc}
     */
    public function removeRelations(Product $productFrom, array $productsTo)
    {
        if (count($productsTo) === 0) {
            return;
        }
        foreach ($productsTo as $productTo) {
            $this->removeRelation($productFrom, $productTo);
        }
        $this->getEntityManager()->flush();
    }
    /**
     * @param Product $productFrom
     * @param Product $productTo
     */
    protected function removeRelation(Product $productFrom, Product $productTo)
    {
        $persistedRelation = $this->getRepository()
            ->findOneBy(['product' => $productFrom, 'relatedItem' => $productTo]);

        if ($persistedRelation === null) {
            return;
        }
        $this->getEntityManager()->remove($persistedRelation);
    }
    /**
     * @param Product $productFrom
     * @param Product $productTo
     * @return bool
     */
    protected function relationAlreadyExists(Product $productFrom, Product $productTo)
    {
        return $this->getRepository()->exists($productFrom, $productTo);
    }
    /**
     * @param Product $productFrom
     * @param Product $productTo
     */
    protected function addRelation(Product $productFrom, Product $productTo)
    {
        $relatedItem = $this->createNewRelation();
        $relatedItem->setProduct($productFrom)
            ->setRelatedItem($productTo);

        $this->getEntityManager()->persist($relatedItem);
    }
    /**
     * @param Product   $productFrom
     * @param Product[] $productsTo
     *
     * @throws \LogicException when functionality is disabled
     * @throws \OverflowException when user tries to add more products that limit allows
     * @throws \InvalidArgumentException When user tries to add related product to itself
     *
     * @return Product[]
     */
    protected function validateRelations(Product $productFrom, array $productsTo)
    {
        $newRelations = [];
        foreach ($productsTo as $productTo) {
            if (!$this->validateRelation($productFrom, $productTo)) {
                continue;
            }
            $newRelations[] = $productTo;
        }
        if (count($newRelations) === 0) {
            return [];
        }
        $numberOfRelations = $this->getRepository()->countRelationsForProduct($productFrom->getId());
        $numberOfRelations += count($newRelations);

        return $newRelations;
    }
    /**
     * @param Product $productFrom
     * @param Product $productTo
     *
     * @throws \InvalidArgumentException When user tries to add related product to itself
     *
     * @return bool
     */
    protected function validateRelation(Product $productFrom, Product $productTo)
    {
        if ($productFrom === $productTo) {
            throw new \InvalidArgumentException('It is not possible to create relations from product to itself.');
        }

        if ($this->relationAlreadyExists($productFrom, $productTo)) {
            return false;
        }

        return true;
    }
    /**
     * {@inheritDoc}
     */
    protected function createNewRelation()
    {
        return new CustomRelatedProduct();
    }
    /**
     * {@inheritDoc}
     */
    protected function getEntityManager()
    {
        return $this->doctrineHelper->getEntityManager(CustomRelatedProduct::class);
    }
    /**
     * {@inheritDoc}
     */
    protected function getRepository()
    {
        return $this->doctrineHelper->getEntityRepository(CustomRelatedProduct::class);
    }    
    public function getCustomRelatedProductEntityManager(){
      return $this->getEntityManager();  
    }
    public function getCustomRelatedInstance(){
      return $this->createNewRelation();  
    }

}