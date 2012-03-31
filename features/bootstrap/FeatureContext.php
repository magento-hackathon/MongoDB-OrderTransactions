<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;

use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

require_once 'mink/autoload.php';

/**
 * Features context.
 */
class FeatureContext extends Behat\Mink\Behat\Context\MinkContext
{
    /**
     * Wait for a period of time specified in milliseconds
     *
     * @Then /^I wait for (?P<num>\d+) milliseconds?$/
     */
    public function iWaitFor($milliseconds)
    {
        $this->getSession()->wait($milliseconds);
    }

    /**
     * Mouse click on an element with specified CSS.
     *
     * @Then /^(?:|I )click the? "(?P<element>[^"]*)" element$/
     */
    public function clickElement($element)
    {
        $node = $this->getSession()->getPage()->find('css', $element);

        if (null === $node) {
            throw new ElementNotFoundException(
                $this->getSession(), 'element', 'css', $element
            );
        }
        $node->click();
    }
}