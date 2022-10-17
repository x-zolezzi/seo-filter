<?php
declare(strict_types=1);

namespace SeoFilter\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use SeoFilter\Model\Table\SeofilterFiltersOrdersTable;

/**
 * SeoFilter\Model\Table\SeofilterFiltersOrdersTable Test Case
 */
class SeofilterFiltersOrdersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \SeoFilter\Model\Table\SeofilterFiltersOrdersTable
     */
    protected $SeofilterFiltersOrders;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.SeoFilter.SeofilterFiltersOrders',
        'plugin.SeoFilter.SeofilterFilters',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('SeofilterFiltersOrders') ? [] : ['className' => SeofilterFiltersOrdersTable::class];
        $this->SeofilterFiltersOrders = $this->getTableLocator()->get('SeofilterFiltersOrders', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->SeofilterFiltersOrders);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \SeoFilter\Model\Table\SeofilterFiltersOrdersTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \SeoFilter\Model\Table\SeofilterFiltersOrdersTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
