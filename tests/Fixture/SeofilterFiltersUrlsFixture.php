<?php
declare(strict_types=1);

namespace SeoFilter\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SeofilterFiltersUrlsFixture
 */
class SeofilterFiltersUrlsFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // phpcs:disable
    public $fields = [
        'id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'seofilter_filter_id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'seo_url' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'seo_titre' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'seo_description' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => false, 'default' => null, 'comment' => ''],
        'modified' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => false, 'default' => null, 'comment' => ''],
        '_indexes' => [
            'seofilter_filter_id' => ['type' => 'index', 'columns' => ['seofilter_filter_id'], 'length' => []],
        ],
        '_constraints' => [
            'seofilter_filters_urls_ibfk_1' => ['type' => 'foreign', 'columns' => ['seofilter_filter_id'], 'references' => ['seofilter_filters', 'id'], 'update' => 'cascade', 'delete' => 'cascade', 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'latin1_swedish_ci'
        ],
    ];
    // phpcs:enable
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'seofilter_filter_id' => 1,
                'seo_url' => 'Lorem ipsum dolor sit amet',
                'seo_titre' => 'Lorem ipsum dolor sit amet',
                'seo_description' => 'Lorem ipsum dolor sit amet',
                'created' => '2022-10-14 14:34:43',
                'modified' => '2022-10-14 14:34:43',
            ],
        ];
        parent::init();
    }
}
