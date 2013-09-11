<?php

namespace Seegno\BootstrapBundle\Twig\Extension;

use Knp\Menu\Util\MenuManipulator;
use Knp\Menu\FactoryInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Silex\Voter\RouteVoter;
use Knp\Menu\Twig\Helper;

/**
 * SeegnoBootstrapBreadcrumbsExtension
 */
class SeegnoBootstrapBreadcrumbsExtension extends \Twig_Extension
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var MatcherInterface
     */
    protected $matcher;

    /**
     * @var RouteVoter
     */
    protected $voter;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var MenuManipulator
     */
    protected $menuManipulator;

    public function __construct(FactoryInterface $factory, MatcherInterface $matcher, RouteVoter $voter, Helper $helper)
    {
        $this->factory = $factory;
        $this->matcher = $matcher;
        $this->helper = $helper;

        $this->matcher->addVoter($voter);
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'seegno_bootstrap_breadcrumb' => new \Twig_Function_Method($this, 'renderHtmlBreadcrumb', array('is_safe' => array('html'))),
        );
    }

    /**
     * Render breadcrumb
     *
     * @param  string $name  The menu name
     *
     * @return string        The HTML
     */
    public function renderHtmlBreadcrumb($name)
    {
        $current = $this->getCurrent($name);

        $menuManipulator = new MenuManipulator;
        $breadcrumb = $menuManipulator->getBreadcrumbsArray($current);

        return $this->environment->render('SeegnoBootstrapBundle:Breadcrumb:layout.html.twig', array(
            'breadcrumb' => $breadcrumb
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'seegno_bootstrap_alerts';
    }

    /**
     * Get current menu item
     *
     * @param  string $name The menu name
     *
     * @return \Knp\Menu\MenuItem
     */
    private function getCurrent($name)
    {
        $menu = $this->helper->get($name);

        $treeIterator = new \RecursiveIteratorIterator(
            new \Knp\Menu\Iterator\RecursiveItemIterator(
                new \ArrayIterator(array($menu))
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $iterator = new \Knp\Menu\Iterator\CurrentItemFilterIterator($treeIterator, $this->matcher);

        // set current as an empty Item in order to avoid exceptions on knp_menu_get
        $current = new \Knp\Menu\MenuItem('', $this->factory);

        foreach ($iterator as $item) {
            $item->setCurrent(true);
            $current = $item;
            break;
        }

        return $current;
    }
}
