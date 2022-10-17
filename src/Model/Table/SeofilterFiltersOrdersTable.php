<?php
declare(strict_types=1);

namespace SeoFilter\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SeofilterFiltersOrders Model
 *
 * @property \SeoFilter\Model\Table\SeofilterFiltersTable&\Cake\ORM\Association\BelongsTo $SeofilterFilters
 *
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder newEmptyEntity()
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder newEntity(array $data, array $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder[] newEntities(array $data, array $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder get($primaryKey, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \SeoFilter\Model\Entity\SeofilterFiltersOrder[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SeofilterFiltersOrdersTable extends Table
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

        $this->setTable('seofilter_filters_orders');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

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
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('model')
            ->maxLength('model', 255)
            ->requirePresence('model', 'create')
            ->notEmptyString('model');

        $validator
            ->scalar('colonne')
            ->maxLength('colonne', 255)
            ->requirePresence('colonne', 'create')
            ->notEmptyString('colonne');

        $validator
            ->scalar('direction')
            ->requirePresence('direction', 'create')
            ->notEmptyString('direction');

        $validator
            ->boolean('ordre')
            ->requirePresence('ordre', 'create')
            ->notEmptyString('ordre');

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
        $rules->add($rules->existsIn(['seofilter_filter_id'], 'SeofilterFilters'), ['errorField' => 'seofilter_filter_id']);

        return $rules;
    }
}
