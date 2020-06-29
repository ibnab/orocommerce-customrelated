<?php

namespace Ibnab\Bundle\CustomRelatedBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Ibnab\Bundle\CustomRelatedBundle\Entity\CustomRelatedProduct;
use Oro\Bundle\ProductBundle\RelatedItem\AbstractAssignerRepositoryInterface;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;

/**
 * Doctrine repository for RelatedProduct entity. Introduces methods to get count of related items, fetch collection of
 * related products and collection of ids of related products.
 */
class CustomRelatedProductRepository extends EntityRepository implements AbstractAssignerRepositoryInterface {

    /**
     * @param Product|int $productFrom
     * @param Product|int $productTo
     * @return bool
     */
    public function exists($productFrom, $productTo) {
        return null !== $this->findOneBy(['product' => $productFrom, 'relatedItem' => $productTo]);
    }

    /**
     * @param int $id
     * @return int
     */
    public function countRelationsForProduct($id) {
        return (int) $this->createQueryBuilder('custom_related_products')
                        ->select('COUNT(custom_related_products.id)')
                        ->where('custom_related_products = :id')
                        ->setParameter(':id', $id)
                        ->getQuery()
                        ->getSingleScalarResult();
    }

    /**
     * @param int $id
     * @param bool $bidirectional
     * @param int|null $limit
     * @return int[]
     */
    public function findRelatedIds($id, $bidirectional, $limit = null, $order = 'position') {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('DISTINCT IDENTITY(crp.relatedItem) as id')
                ->from(CustomRelatedProduct::class, 'crp');
        if (is_array($id)) {
            $qb->where($qb->expr()->in('crp.product', ':id'));
        } else {
            $qb->where($qb->expr()->eq('crp.product', ':id'));
        }
        $qb->setParameter('id', $id);
        if ($order == 'total'):
            $qb->orderBy('crp.total');
        else:
            $qb->orderBy('crp.relatedItem');
        endif;

        if ($limit) {
            $qb->setMaxResults($limit);
        }
        $productIds = $qb->getQuery()->getArrayResult();
        $productIds = array_column($productIds, 'id');
        if ($bidirectional) {
            if ($limit === null || count($productIds) < $limit) {
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->select('DISTINCT IDENTITY(crp.product) as id')
                        ->from(CustomRelatedProduct::class, 'crp')
                        ->where($qb->expr()->eq('crp.relatedItem', ':id'))
                        ->setParameter('id', $id);
                        if ($order == 'total'):
                           $qb->orderBy('crp.total');
                        else:
                           $qb->orderBy('crp.product');
                        endif;
                if ($productIds) {
                    $qb->andWhere($qb->expr()->notIn('crp.product', ':alreadySelectedIds'))
                            ->setParameter('alreadySelectedIds', $productIds);
                }
                if ($limit) {
                    $qb->setMaxResults($limit - count($productIds));
                }
                $biProductIds = $qb->getQuery()->getArrayResult();
                $biProductIds = array_column($biProductIds, 'id');
                $productIds = array_merge($productIds, $biProductIds);
            }
        }

        return $productIds;
    }

    /**
     * @param int $id
     * @return CustomRelatedProduct[]
     */
    public function findRelatedLinks($id) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('crp')
                ->from(CustomRelatedProduct::class, 'crp')
                ->where($qb->expr()->eq('crp.product', ':id'))
                ->setParameter('id', $id);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $id
     * @param bool $bidirectional
     * @param int|null $limit
     * @return Product[]
     */
    public function findRelated($id, $bidirectional, $limit = null, $order = 'position') {
        $productIds = $this->findRelatedIds($id, $bidirectional, $limit, $order);

        $products = [];
        if ($productIds) {
            $products = $this->getEntityManager()
                    ->getRepository(Product::class)
                    ->findBy(['id' => $productIds], ['id' => 'ASC'], $limit);
        }

        return $products;
    }

    public function fullEmpty() {
        $em = $this->getEntityManager();
        $cmd = $em->getClassMetadata(CustomRelatedProduct::class);
        $connection = $em->getConnection();
        $connection->beginTransaction();
        try {
            $connection->query('TRUNCATE TABLE ' . $cmd->getTableName());
            $connection->commit();
            $em->flush();
            $em->clear();
        } catch (\Exception $e) {
            $connection->rollback();
        }
    }

    public function fullInitial() {
        $em = $this->getEntityManager();
        $cmd = $em->getClassMetadata(CustomRelatedProduct::class);
        $connection = $em->getConnection();
        $connection->beginTransaction();
        try {
            $connection->query('update ' . $cmd->getTableName() . ' set total = 1');
            $connection->commit();
            $em->flush();
            $em->clear();
        } catch (\Exception $e) {
            $connection->rollback();
        }
    }

}
