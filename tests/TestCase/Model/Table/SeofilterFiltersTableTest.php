<?php
declare(strict_types=1);

namespace SeoFilter\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use SeoFilter\Model\Table\SeofilterFiltersTable;

/**
 * SeoFilter\Model\Table\SeofilterFiltersTable Test Case
 */
class SeofilterFiltersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \SeoFilter\Model\Table\SeofilterFiltersTable
     */
    protected $SeofilterFilters;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.SeoFilter.SeofilterFilters',
        'plugin.SeoFilter.SeofilterFiltersCriteres',
        'plugin.SeoFilter.SeofilterFiltersOrders',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('SeofilterFilters') ? [] : ['className' => SeofilterFiltersTable::class];
        $this->SeofilterFilters = $this->getTableLocator()->get('SeofilterFilters', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->SeofilterFilters);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \SeoFilter\Model\Table\SeofilterFiltersTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \SeoFilter\Model\Table\SeofilterFiltersTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
