<?php
declare(strict_types=1);

namespace SeoFilter\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use SeoFilter\Model\Table\SeofilterFiltersCriteresTable;

/**
 * SeoFilter\Model\Table\SeofilterFiltersCriteresTable Test Case
 */
class SeofilterFiltersCriteresTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \SeoFilter\Model\Table\SeofilterFiltersCriteresTable
     */
    protected $SeofilterFiltersCriteres;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.SeoFilter.SeofilterFiltersCriteres',
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
        $config = $this->getTableLocator()->exists('SeofilterFiltersCriteres') ? [] : ['className' => SeofilterFiltersCriteresTable::class];
        $this->SeofilterFiltersCriteres = $this->getTableLocator()->get('SeofilterFiltersCriteres', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->SeofilterFiltersCriteres);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \SeoFilter\Model\Table\SeofilterFiltersCriteresTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \SeoFilter\Model\Table\SeofilterFiltersCriteresTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
