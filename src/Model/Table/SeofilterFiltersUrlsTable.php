<?php
declare(strict_types=1);

namespace SeoFilter\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SeofilterFiltersUrls Model
 *
 * @property \SeoFilter\Model\Table\SeofilterFiltersTable&\Cake\ORM\Association\BelongsTo $SeofilterFilters
 *
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl newEmptyEntity()
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl newEntity(array $data, array $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl[] newEntities(array $data, array $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl get($primaryKey, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersUrl[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SeofilterFiltersUrlsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('seofilter_filters_urls');

        $this->addBehavior('Timestamp');

        $this->belongsTo('SeofilterFilters', [
            'foreignKey' => 'seofilter_filter_id',
            'joinType' => 'INNER',
            'className' => 'SeoFilter.SeofilterFilters',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->requirePresence('id', 'create')
            ->notEmptyString('id');

        $validator
            ->scalar('seo_url')
            ->maxLength('seo_url', 255)
            ->requirePresence('seo_url', 'create')
            ->notEmptyString('seo_url');

        $validator
            ->scalar('seo_titre')
            ->maxLength('seo_titre', 255)
            ->requirePresence('seo_titre', 'create')
            ->notEmptyString('seo_titre');

        $validator
            ->scalar('seo_description')
            ->maxLength('seo_description', 255)
            ->requirePresence('seo_description', 'create')
            ->notEmptyString('seo_description');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['seofilter_filter_id'], 'SeofilterFilters'), ['errorField' => 'seofilter_filter_id']);

        return $rules;
    }
}
