<?php
namespace Ibnab\Bundle\CustomRelatedBundle\EventListener;
use Oro\Bundle\FormBundle\Event\FormHandler\FormProcessEvent;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;
use Twig\Environment;
use Symfony\Component\Form\FormInterface;
use Oro\Bundle\ProductBundle\Form\Handler\RelatedItemsHandler;
/**
 * Adds related product information (tabs, grids, forms) to the product edit page.
 */
class RelatedCustomRelatedEditListener
{
    const RELATED_ITEMS_ID = 'relatedItems';
    /** @var int */
    const BLOCK_PRIORITY = 1500;
    /** @var TranslatorInterface */
    private $translator;
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;
    /** @var RelatedItemsHandler */
    private $relatedItemsHandler;
    /**
     * @param TranslatorInterface               $translator
     * @param RelatedItemConfigHelper           $relatedItemConfigHelper
     * @param AuthorizationCheckerInterface     $authorizationChecker
     */
    public function __construct(
        TranslatorInterface $translator,
        AuthorizationCheckerInterface $authorizationChecker,
        RelatedItemsHandler $relatedItemsHandler
    ) {
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
        $this->relatedItemsHandler = $relatedItemsHandler;
    }
    /**
     * @param BeforeListRenderEvent $event
     */
    public function onProductEdit(BeforeListRenderEvent $event)
    {
        $twigEnv = $event->getEnvironment();
        $tabs = [];
        $grids = [];
            $tabs[] = [
                'id' => 'customrelated-products-block',
                'label' => $this->translator->trans('ibnab.customrelated.tabs.customrelatedProducts.label')
            ];
            $grids[] = $this->getCustomRelatedProductsEditBlock($event, $twigEnv);
            $grids = array_merge([$this->renderTabs($twigEnv, $tabs)], $grids);
        if (count($grids) > 0) {
            $this->addEditPageBlock($event->getScrollData(), $grids);
        }
    }
    /**
     * @param ScrollData $scrollData
     * @param string[] $htmlBlocks
     */
    private function addEditPageBlock(ScrollData $scrollData, array $htmlBlocks)
    {
        $subBlock = $scrollData->addSubBlock(self::RELATED_ITEMS_ID);
        $scrollData->addSubBlockData(
            self::RELATED_ITEMS_ID,
            $subBlock,
            implode('', $htmlBlocks),
            'relatedItems'
        );
    }
    /**
     * @param BeforeListRenderEvent $event
     * @param Environment $twigEnv
     * @return string
     */
    private function getCustomRelatedProductsEditBlock(BeforeListRenderEvent $event, Environment $twigEnv)
    {
        return $twigEnv->render(
            '@IbnabCustomRelated/CustomRelated/customrelatedProducts.html.twig',
            [
                'form' => $event->getFormView(),
                'entity' => $event->getEntity(),
                'relatedProductsLimit' => 8
            ]
        );
    }
    /**
     * @param Environment $twigEnv
     * @param array $tabs
     * @return string
     */
    private function renderTabs(Environment $twigEnv, array $tabs)
    {
        return $twigEnv->render(
            '@OroProduct/Product/RelatedItems/tabs.html.twig',
            [
                'relatedItemsTabsItems' => $tabs
            ]
        );
    }
    /**
     * @param FormProcessEvent $event
     */
    public function onFormDataSet(FormProcessEvent $event)
    {
            $event->getForm()->add(
                'appendCustomRelated',
                EntityIdentifierType::class,
                [
                    'class' => Product::class,
                    'required' => false,
                    'mapped' => false,
                    'multiple' => true,
                ]
            );
            $event->getForm()->add(
                'removeCustomRelated',
                EntityIdentifierType::class,
                [
                    'class' => Product::class,
                    'required' => false,
                    'mapped' => false,
                    'multiple' => true,
                ]
            );
    }
    /**
     * @param AfterFormProcessEvent $event
     */
    public function onPostSubmit(AfterFormProcessEvent $event)
    {        
        $form = $event->getForm();
        $targetEntity = $form->getData();
        $this->saveCustomRelatedProducts($form,$targetEntity);
    }
    /**
     * @param FormInterface $form
     * @param Product $entity
     * @return bool
     */
    private function saveCustomRelatedProducts(FormInterface $form, Product $entity)
    {
        return $this->saveRelatedItems(
            $form,
            $entity,
            'customrelatedProducts',
            'appendCustomRelated',
            'removeCustomRelated'
        );
    }
    /**
     * @param FormInterface $form
     * @param Product $entity
     * @param string $assignerName
     * @param string $appendItemsFieldName
     * @param string $removeItemsFieldName
     * @return bool
     */
    private function saveRelatedItems(
        FormInterface $form,
        Product $entity,
        $assignerName,
        $appendItemsFieldName,
        $removeItemsFieldName
    ) {
        if (!$form->has($appendItemsFieldName) && !$form->has($removeItemsFieldName)) {
            return true;
        }
        //var_dump($form->get($appendItemsFieldName)->getData());die();
        return $this->relatedItemsHandler->process(
            $assignerName,
            $entity,
            $form->get($appendItemsFieldName),
            $form->get($removeItemsFieldName)
        );
    }
}
