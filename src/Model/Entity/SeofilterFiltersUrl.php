<?php
declare(strict_types=1);

namespace SeoFilter\Model\Entity;

use Cake\ORM\Entity;

/**
 * SeofilterFiltersUrl Entity
 *
 * @property int $id
 * @property int $seofilter_filter_id
 * @property string $seo_url
 * @property string $seo_titre
 * @property string $seo_description
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \SeoFilter\Model\Entity\SeofilterFilter $seofilter_filter
 */
class SeofilterFiltersUrl extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'id' => true,
        'seofilter_filter_id' => true,
        'seo_url' => true,
        'seo_titre' => true,
        'seo_description' => true,
        'created' => true,
        'modified' => true,
        'seofilter_filter' => true,
    ];
}
