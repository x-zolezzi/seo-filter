<?php

declare(strict_types=1);

namespace SeoFilter\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SeofilterFilters Model
 *
 * @property \SeoFilter\Model\Table\SeofilterFiltersCriteresTable&\Cake\ORM\Association\HasMany $SeofilterFiltersCriteres
 * @property \SeoFilter\Model\Table\SeofilterFiltersOrdersTable&\Cake\ORM\Association\HasMany $SeofilterFiltersOrders
 *
 * @method \SeoFilter\Model\Entity\SeofilterFilter newEmptyEntity()
 * @method \SeoFilter\Model\Entity\SeofilterFilter newEntity(array $data, array $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFilter[] newEntities(array $data, array $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFilter get($primaryKey, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFilter findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFilter patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFilter[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFilter|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFilter saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFilter[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFilter[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFilter[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFilter[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SeofilterFiltersTable extends Table
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

        $this->setTable('seofilter_filters');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('SeofilterFiltersCriteres', [
            'foreignKey' => 'seofilter_filter_id',
            'className' => 'SeoFilter.SeofilterFiltersCriteres',
        ]);
        $this->hasMany('SeofilterFiltersOrders', [
            'foreignKey' => 'seofilter_filter_id',
            'className' => 'SeoFilter.SeofilterFiltersOrders',
        ]);
        $this->hasMany('SeofilterFiltersUrls', [
            'foreignKey' => 'seofilter_filter_id',
            'className' => 'SeoFilter.SeofilterFiltersUrls',
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
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 255)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug')
            ->add('slug', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('controller')
            ->maxLength('controller', 255)
            ->requirePresence('controller', 'create')
            ->notEmptyString('controller');

        $validator
            ->scalar('action')
            ->maxLength('action', 255)
            ->requirePresence('action', 'create')
            ->notEmptyString('action');

        $validator
            ->scalar('model')
            ->maxLength('model', 255)
            ->requirePresence('model', 'create')
            ->notEmptyString('model');

        $validator
            ->scalar('function_find')
            ->maxLength('function_find', 255)
            ->requirePresence('function_find', 'create')
            ->notEmptyString('function_find');

        $validator
            ->scalar('element')
            ->maxLength('element', 255)
            ->requirePresence('element', 'create')
            ->notEmptyString('element');

        $validator
            ->scalar('titre')
            ->maxLength('titre', 255)
            ->allowEmptyString('titre');

        $validator
            ->scalar('seo_titre')
            ->maxLength('seo_titre', 255)
            ->allowEmptyString('seo_titre');

        $validator
            ->scalar('seo_description')
            ->maxLength('seo_description', 370)
            ->allowEmptyString('seo_description');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

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
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);

        return $rules;
    }
}