<?php

namespace Ibnab\Bundle\CustomRelatedBundle\Twig;

use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\RelatedItem\FinderStrategyInterface;
use Ibnab\Bundle\CustomRelatedBundle\RelatedItem\FinderDatabaseStrategy as CustomRelatedProductFinderDatabaseStrategy;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class ProductExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    const NAME = 'ibnab_customrelated';

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }



    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'get_customrelated_products_ids',
                [$this, 'getCustomrelatedProductsIds']
            )
        ];
    }

 

    /**
     * @param Product $product
     *
     * @return int[]
     */
    public function getCustomrelatedProductsIds(Product $product)
    {
        return $this->getRelatedItemsIds(
            $product,
            $this->container->get('ibnab_customrelated.related_item.product.finder_strategy')
        );
    }





    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param Product $product
     * @param FinderStrategyInterface $finderStrategy
     * @return \int[]
     */
    private function getRelatedItemsIds(Product $product, FinderStrategyInterface $finderStrategy)
    {
        
        return $finderStrategy->findIds($product, false);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'ibnab_customrelated.related_item.product.finder_strategy' => CustomRelatedProductFinderDatabaseStrategy::class,
        ];
    }
}
