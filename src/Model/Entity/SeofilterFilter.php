<?php
declare(strict_types=1);

namespace SeoFilter\Model\Entity;

use Cake\ORM\Entity;

/**
 * SeofilterFilter Entity
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $controller
 * @property string $action
 * @property string $model
 * @property string $function_find
 * @property string $element
 * @property string|null $titre
 * @property string|null $seo_titre
 * @property string|null $seo_description
 * @property bool $is_active
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \SeoFilter\Model\Entity\SeofilterFiltersCritere[] $seofilter_filters_criteres
 * @property \SeoFilter\Model\Entity\SeofilterFiltersOrder[] $seofilter_filters_orders
 */
class SeofilterFilter extends Entity
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
        'name' => true,
        'slug' => true,
        'controller' => true,
        'action' => true,
        'model' => true,
        'function_find' => true,
        'element' => true,
        'titre' => true,
        'seo_titre' => true,
        'seo_description' => true,
        'is_active' => true,
        'created' => true,
        'modified' => true,
        'seofilter_filters_criteres' => true,
        'seofilter_filters_orders' => true,
    ];
}
