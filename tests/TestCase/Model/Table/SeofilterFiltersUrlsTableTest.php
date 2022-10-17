<?php
declare(strict_types=1);

namespace SeoFilter\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use SeoFilter\Model\Table\SeofilterFiltersUrlsTable;

/**
 * SeoFilter\Model\Table\SeofilterFiltersUrlsTable Test Case
 */
class SeofilterFiltersUrlsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \SeoFilter\Model\Table\SeofilterFiltersUrlsTable
     */
    protected $SeofilterFiltersUrls;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.SeoFilter.SeofilterFiltersUrls',
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
        $config = $this->getTableLocator()->exists('SeofilterFiltersUrls') ? [] : ['className' => SeofilterFiltersUrlsTable::class];
        $this->SeofilterFiltersUrls = $this->getTableLocator()->get('SeofilterFiltersUrls', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->SeofilterFiltersUrls);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \SeoFilter\Model\Table\SeofilterFiltersUrlsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \SeoFilter\Model\Table\SeofilterFiltersUrlsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
