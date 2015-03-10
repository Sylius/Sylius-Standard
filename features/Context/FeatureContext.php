<?php

namespace Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;

use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\Common\Util\Inflector;

class FeatureContext extends RawMinkContext implements KernelAwareContext
{
    private $kernel;

    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel HttpKernel instance
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given /^I am on the ([\w\s]+)( page)?$/
     * @When /^I go to the ([\w\s]+)( page)?$/
     */
    public function iAmOnThePage($page)
    {
        $this->getSession()->visit($this->generatePageUrl($page, $parameters));
    }

    /**
     * Returns Container instance.
     *
     * @return ContainerInterface
     */
    private function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * Generates url with Router.
     *
     * @param string  $route
     * @param array   $parameters
     * @param Boolean $absolute
     *
     * @return string
     */
    private function generateUrl($route, array $parameters = array(), $absolute = false)
    {
        return $this->getContainer()->get('router')->generate($route, $parameters, $absolute);
    }

    /**
     * Generate page url from name and parameters.
     *
     * @param string $page
     * @param array  $parameters
     *
     * @return string
     */
    private function generatePageUrl($page, array $parameters = array())
    {
        $parts = explode(' ', trim($page), 2);

        if (2 === count($parts)) {
            $parts[1] = Inflector::camelize($parts[1]);
        }

        $route  = implode('_', $parts);
        $routes = $this->getContainer()->get('router')->getRouteCollection();

        if (null === $routes->get($route)) {
            $route = 'app_'.$route;
        }

        return $this->getMinkParameter('base_url').$this->generateUrl($route, $parameters);
    }
}
