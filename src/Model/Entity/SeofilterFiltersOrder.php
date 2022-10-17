<?php
declare(strict_types=1);

namespace SeoFilter\Model\Entity;

use Cake\ORM\Entity;

/**
 * SeofilterFiltersOrder Entity
 *
 * @property int $id
 * @property int $seofilter_filter_id
 * @property string $name
 * @property string $model
 * @property string $colonne
 * @property string $direction
 * @property bool $ordre
 * @property bool $is_active
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \SeoFilter\Model\Entity\SeofilterFilter $seofilter_filter
 */
class SeofilterFiltersOrder extends Entity
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
        'seofilter_filter_id' => true,
        'name' => true,
        'model' => true,
        'colonne' => true,
        'direction' => true,
        'ordre' => true,
        'is_active' => true,
        'created' => true,
        'modified' => true,
        'seofilter_filter' => true,
    ];
}
