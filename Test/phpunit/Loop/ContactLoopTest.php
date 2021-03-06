<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/
/*************************************************************************************/

namespace Dealer\Test\Loop;


use Dealer\Loop\ContactLoop;
use Dealer\Model\Dealer;
use Dealer\Model\DealerContact;
use Dealer\Service\ContactService;
use Dealer\Service\DealerService;
use Dealer\Test\PhpUnit\Base\AbstractPropelTest;
use Propel\Runtime\Util\PropelModelPager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ContactLoopTest extends AbstractPropelTest
{
    /** @var  ContactLoop $loop */
    protected $loop;

    /** @var  Dealer $dealer */
    protected $dealer;

    /** @var  DealerContact $dealer */
    protected $contact;

    /**
     * Expected possible values for the order loop argument.
     * @var array
     */
    protected static $VALID_ORDER = [
        'id',
        'id-reverse',
        'label',
        'label-reverse',
    ];

    /**
     * @inheritDoc
     */
    protected function buildContainer(ContainerBuilder $container)
    {

    }

    public function setUp()
    {
        $this->loop = new ContactLoop($this->container);

        $this->mockEventDispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->setMethods(['dispatch'])
            ->getMock();

        /* Create Test Dealer */
        $dealerService = new DealerService();
        $dealerService->setDispatcher($this->mockEventDispatcher);
        $this->dealer = $dealerService->createFromArray($this->dataDealerRequire(), "fr_FR");

        /* Create Test Contact */
        $contactService = new ContactService();
        $contactService->setDispatcher($this->mockEventDispatcher);
        $this->contact = $contactService->createFromArray($this->dataContactRequire(),"fr_FR");
    }

    /**
     * @covers \Dealer\Loop\ContactLoop::initializeArgs()
     */
    public function testHasNoMandatoryArguments()
    {
        $this->loop->initializeArgs([]);
    }

    /**
     * @covers \Dealer\Loop\ContactLoop::initializeArgs()
     */
    public function testAcceptsAllOrderArguments()
    {
        foreach (static::$VALID_ORDER as $order) {
            $this->loop->initializeArgs(["order" => $order]);
        }
    }

    /**
     * @covers \Dealer\Loop\ContactLoop::initializeArgs()
     */
    public function testAcceptsAllArguments()
    {
        $this->loop->initializeArgs($this->getTestArg());
    }

    /**
     * @covers \Dealer\Loop\ContactLoop::buildModelCriteria()
     * @covers \Dealer\Loop\ContactLoop::exec()
     * @covers \Dealer\Loop\ContactLoop::parseResults()
     */
    public function testHasExpectedOutput()
    {
        $this->loop->initializeArgs($this->getTestArg());
        $loopResult = $this->loop->exec(
            new PropelModelPager($this->loop->buildModelCriteria())
        );

        $this->assertEquals(1,$loopResult->getCount());
        $loopResult->rewind();
        $loopResultRow = $loopResult->current();
        $this->assertEquals($this->contact->getId(), $loopResultRow->get("ID"));
        $this->assertEquals($this->contact->getDealerId(), $loopResultRow->get("DEALER_ID"));
        $this->assertEquals($this->contact->getIsDefault(), $loopResultRow->get("IS_DEFAULT"));
    }

    protected function getTestArg()
    {
        return [
            "id" => $this->contact->getId(),
            "dealer_id" => $this->contact->getDealerId(),
        ];
    }

    protected function dataContactRequire()
    {
        return [
            "label" => "Openstudio",
            "dealer_id" => $this->dealer->getId()
        ];
    }

    protected function dataDealerRequire()
    {
        return [
            "title" => "Openstudio",
            "address1" => "5 rue jean rochon",
            "zipcode" => "63000",
            "city" => "test",
            "country_id" => "64",
        ];
    }
}
