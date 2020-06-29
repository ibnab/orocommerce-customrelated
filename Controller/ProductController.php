<?php
namespace Ibnab\Bundle\CustomRelatedBundle\Controller;
use Oro\Bundle\ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
/**
 * CRUD controller for the Product entity.
 */
class ProductController extends AbstractController
{
    /**
     * @Route(
     *     "/get-possible-products-for-customrelated-products/{id}",
     *     name="ibnab_customrelated_possible_products_for_customrelated_products",
     *     requirements={"id"="\d+"}
     * )
     * @Template(template="IbnabCustomRelatedBundle:Product:selectCustomRelatedProducts.html.twig")
     *
     * @param Product $product
     * @return array
     */
    public function getPossibleProductsForCustomRelatedProductsAction(Product $product)
    {
        return ['product' => $product];
    }
}
