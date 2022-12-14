<?php

namespace SeoFilter\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Http\Exception\RedirectException;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\Query;
use Cake\Routing\Router;

class SeoFilterComponent extends Component
{

    private array $paginate = [
        'enabled' => false,
        'items_per_page' => 0,
    ];

    protected array $criteres = []; // QueryBuilder
    protected array $criteresFilter = []; //

    protected array $order = []; // paramètres d'ordre de la requête

    private bool $countResults = true;

    private array $config = [];

    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        if(isset($config['paginate']))
            $this->paginate = [
                'enabled' => $config['paginate']['enabled'],
                'items_per_page' => $config['paginate']['items_per_page'] > 0 ? $config['paginate']['items_per_page'] : 1
            ];

        if(isset($config['countResults']))
            $this->countResults = $config['countResults'];

        $this->config = [
            'paginate' => $this->paginate,
            'countResults' => $this->countResults
        ];

        parent::__construct($registry, $config);
    }

    public function initialize(array $config): void
    {
        $this->_setCriteres();
        $this->_setOrder();
        parent::initialize($config);
    }

    private function _getStdCondition(
        &$conditions,
        $filterValue,
        $filterKey,
        $critere
    ): void{
        $operateur = '';

        if(is_array($filterValue)){ // Traitement tableau
            $operateur = ' IN';
            $filterValue = $filterValue;
        }else{ // Traitement strings
            if (preg_match('@([0-9]+):([0-9]+)@', $filterValue, $matches)) {
                $operateur = ' >=';
                $conditions[$critere->model . '.' . $critere->colonne . $operateur] = $matches[1];
                $operateur = ' <=';
                $filterValue = $matches[2];
            } elseif (preg_match('@sup([0-9]+)@', $filterValue, $matches)) {
                $operateur = ' >';
                $filterValue = $matches[1];
            } elseif (preg_match('@supeq([0-9]+)@', $filterValue, $matches)) {
                $operateur = ' >=';
                $filterValue = $matches[1];
            } elseif (preg_match('@inf([0-9]+)@', $filterValue, $matches)) {
                $operateur = ' <';
                $filterValue = $matches[1];
            } elseif (preg_match('@infeq([0-9]+)@', $filterValue, $matches)) {
                $operateur = ' <=';
                $filterValue = $matches[1];
            }
        }
        $conditions[$critere->model . '.' . $critere->colonne . $operateur] = $filterValue;
    }

    // Un range ne devrait contenir qu'une ou deux valeurs, pas de traitement regex particulier à prévoir
    private function _getRangeConditions(
        &$conditions,
        $filterValue,
        $filterKey,
        $critere
    ): void{
        if(!is_array($filterValue)){
            $filterValue = ['min' => $filterValue, 'max' => $filterValue];
        }

        $conditions[$critere->model . '.' . $critere->colonne . ' <='] = $filterValue['max'];
//        $conditions[$critere->model . '.' . $critere->colonne . ' >='] = $filterValue['min'];
    }

    private function _getBooleanConditions(
        &$conditions,
        $filterValue,
        $filterKey,
        $critere
    ): void{
        $conditions['OR'][] = [$critere->model . '.' . $critere->colonne => true];
    }

    public function getConfig(?string $key = null, $default = null)
    {
        if($key){
            if(!array_key_exists($key, $this->config)){
                return false;
            }

            return $this->config[$key];
        }

        return $this->config;
    }

    private function _setCriteres(){

        $this->getController()->loadModel('SeoFilter.SeofilterFilters');
        $filtre_slug = $this->getController()->getRequest()->getParam('slug_seo_filter');
        $filtre = $this->getController()->SeofilterFilters->find()->where(['SeofilterFilters.slug' => $filtre_slug, 'SeofilterFilters.is_active' => true])->first();

        if($this->getController()->getRequest()->is('GET')){
            $filtresParam = $filtres ?? $this->getController()->getRequest()->getParam('filtres');
            if(empty($filtresParam)) return [];
            $filters = explode('/', $filtresParam);
            if(empty($filtre_slug)) return [];
        }else{
            $filters = $this->getController()->getRequest()->getData('seofilter_filters_criteres');
        }

        $this->getController()->loadModel('SeoFilter.SeofilterFiltersCriteres');
        foreach ($filters as $i => $filter){
            if($this->getController()->getRequest()->is('GET')){
                $explode = explode('-', $filter, 2);
                if (!isset($explode[1])) {
                    continue;
                }

                $filterKey = $explode[0];
                $filterValue = $explode[1];
            }else{
                $filterKey = $i;
                $filterValue = $filter;
            }

            $critere = $this->getController()->SeofilterFiltersCriteres->find()
                ->where([
                    'SeofilterFiltersCriteres.seofilter_filter_id' => $filtre->id,
                    'SeofilterFiltersCriteres.slug' => $filterKey,
                    'SeofilterFiltersCriteres.is_active' => true
                ])->first();

            if(empty($critere) || empty($filterValue)){
                // Critere non trouvé => ?
                continue;
            }

            $this->criteresFilter[$critere->slug] =  $filterValue;
            $this->criteres[$critere->slug] = $critere;
        }
    }

    public function applyConditions(){
        $conditions = [];
        foreach ($this->criteres as $critere){
            if($critere->method !== 'WHERE'){
                continue;
            }

            $filterValue = $this->criteresFilter[$critere->slug];
            $filterKey = $critere->slug;

            //@todo: tester les types de critères (std, bool, range, association, ...) et appeler la méthode qui correspond
            if($critere->critere_type === 'CHECKBOX'){
                $this->_getStdCondition($conditions, $filterValue, $filterKey, $critere);
            }

            if($critere->critere_type === 'RANGE'){
                $this->_getRangeConditions($conditions, $filterValue, $filterKey, $critere);
            }

            if($critere->critere_type === 'BOOLEAN'){
                $this->_getBooleanConditions($conditions, $filterValue, $filterKey, $critere);
            }
        }

        return $conditions;
    }

    public function applyMatching(){
        foreach ($this->criteres as $critere){
            if($critere->method !== 'MATCHING'){
                continue;
            }


        }
    }

    public function getConditions($forceRedirect = true)
    {
        // TODO: mettre dans le app.php
        if ($forceRedirect) {
            //Controle ordre des critères => Redirection 301
            $url = Router::url(null, true);
            $validUrl = $this->getUrl($this->getController()->getRequest()->getParam('slug_seo_filter'));
            if ($validUrl != $url) {
                // TODO: mettre nom route dans app.php
                throw new RedirectException($validUrl, 301);
            }
        }

        return $this->applyConditions();
    }

    public function getCriteresValues()
    {
        $criteres = [];
        $filtresParam = $filtres ?? $this->getController()->getRequest()->getParam('filtres');
        if(empty($filtresParam)) return [];

        $filters = explode('/', $filtresParam);
        $filtre_slug = $slug_seo_filter ?? $this->getController()->getRequest()->getParam('slug_seo_filter');

        if(empty($filtre_slug) || empty($filters)) return [];

        foreach ($filters as $filter) {
            $explode = explode('-', $filter, 2);
            if (!isset($explode[1])) {
                continue;
            }
            $filterKey = $explode[0];
            $filterValue = $explode[1];

            if (preg_match('@([0-9]+):([0-9]+)@', $filterValue, $matches)) {
                $operateur = ' >=';
                $criteres[] = ['slug' => $filterKey, 'operateur' => $operateur, 'value' => $filterValue];
                $operateur = ' <=';
            } elseif (preg_match('@sup([0-9]+)@', $filterValue, $matches)) {
                $operateur = ' >';
            } elseif (preg_match('@supeq([0-9]+)@', $filterValue, $matches)) {
                $operateur = ' >=';
            } elseif (preg_match('@inf([0-9]+)@', $filterValue, $matches)) {
                $operateur = ' <';
            } elseif (preg_match('@infeq([0-9]+)@', $filterValue, $matches)) {
                $operateur = ' <=';
            } elseif (strpos($filterValue, ' ') !== false) {
                $operateur = ' IN';
                $filterValue = explode(' ', $filterValue);
            } elseif (strpos($filterValue, '+') !== false) {
                $operateur = ' IN';
                $filterValue = explode('+', $filterValue);
            } else {
                $operateur = '=';
            }
            $criteres[] = ['slug' => $filterKey, 'operateur' => $operateur, 'value' => $filterValue];
        }
        return $criteres;
    }

    public function getFilterData(){

        $this->getController()->loadModel('SeoFilter.SeofilterFilters');
        $filtre = $this->getController()->SeofilterFilters->find()
            ->contain([
                'SeofilterFiltersCriteres' => function(Query $q): Query{
                    return $q->where(['is_active' => true]);
                },
                'SeofilterFiltersOrders'
            ])
            ->where([
                'SeofilterFilters.slug' => $this->getController()->getRequest()->getParam('slug_seo_filter'),
                'SeofilterFilters.is_active' => true
            ])->first();
        $this->getController()->set('seo_filter', $filtre);

        $values_for_seofilter_filters_criteres = [];

        foreach ($filtre->seofilter_filters_criteres as $seofilter_filters_criteres) {
            $this->getController()->loadModel($seofilter_filters_criteres->model);
            if(!empty($seofilter_filters_criteres->function_find_values)){
                //@todo: permettre de mettre le nom OU l'id du filtre en clé
                $values_for_seofilter_filters_criteres[$seofilter_filters_criteres->slug] =
                    $this->getController()->{$seofilter_filters_criteres->model}
                        ->{$seofilter_filters_criteres->function_find_values}();

            }
        }
        $this->getController()->set('values_for_seofilter_filters_criteres', $values_for_seofilter_filters_criteres);

        $seo_filter_criteres_values = $this->getCriteresValues();
        $this->getController()->set('seo_filter_criteres_values', $seo_filter_criteres_values);

        if (!empty($this->getController()->getRequest()->getParam('filtres'))) {
            $this->getController()->loadModel('SeoFilter.SeofilterFiltersUrls');
            $seo_filter_urls = $this->getController()->SeofilterFiltersUrls->find()
                ->where([
                    'SeofilterFiltersUrls.seo_url' => $this->getController()->getRequest()->getParam('filtres'),
                    'SeofilterFiltersUrls.seofilter_filter_id' => $filtre->id
                ])->first();
            $this->getController()->set('seo_filter_urls', $seo_filter_urls);
        }
    }

    public function getUrl($filtre_slug, $full_url = true)
    {

        ksort($this->criteresFilter);
        $criteresPourUrl = $this->criteresFilter;

        $str = '';
        foreach ($criteresPourUrl as $k => $critere){
            if(is_array($critere)){
                $criteresPourUrl[$k] = implode('+', $critere);
            }

            $str .= $k . '-' . $criteresPourUrl[$k] . '/';
        }

        if ($full_url) {
            $order = $this->order;
            if (!empty($order)) {
                $order = ['order' =>  key($order) . '-' . $order[key($order)]];
            }
            return Router::url(['_name' => $filtre_slug . (empty($str) ? '_empty' : ''), 'filtres' => $str, 'slug_seo_filter' => $filtre_slug, '?' => $order], true);
        } else {
            return '/' . $str;
        }
    }

    public function getCriteres($criteres = [])
    {
        $return = [];
        if (!empty($criteres)) {
            $filtre_slug = $this->getController()->getRequest()->getParam('slug_seo_filter');
            if (!empty($filtre_slug)) {
                $this->getController()->loadModel('SeoFilter.SeofilterFilters');
                $filtre = $this->getController()->SeofilterFilters->find()->where(['SeofilterFilters.slug' => $filtre_slug, 'SeofilterFilters.is_active' => true])->first();

                foreach ($criteres as $filterKey => $filterValue) {
                    $this->getController()->loadModel('SeoFilter.SeofilterFiltersCriteres');
                    $critere = $this->getController()->SeofilterFiltersCriteres->find()->where(['SeofilterFiltersCriteres.seofilter_filter_id' => $filtre->id, 'SeofilterFiltersCriteres.slug' => $filterKey, 'SeofilterFiltersCriteres.is_active' => true])->first();
                    if (is_array($filterValue)) {
                        sort($filterValue);
                        $return[$critere->id] = $critere->slug . '-' . implode(' ', $filterValue);
                    } else {
                        $return[$critere->id] = $critere->slug . '-' . $filterValue;
                    }
                }
            }
        }
        return $return;
    }

    private function _setOrder(): self{
        $orderParam =  $this->getController()->getRequest()->getQuery('order') ?? $this->getController()->getRequest()->getData('order');
        $order = [];
        if (!empty($orderParam)) {
            $explode = explode('-', $orderParam);
            if (count($explode) == 2 && in_array(strtoupper($explode[1]), ['ASC', 'DESC'])) {
                $order[$explode[0]] = strtoupper($explode[1]);
            }
        }

        $this->order = $order;

        return $this;
    }

    public function applyFilters(Query &$query): Query{

        $conditions = $this->getConditions(!$this->getController()->getRequest()->is('ajax'));
        $query->andWhere($conditions);
        if(!empty($this->order)){
            $query->order($this->order);
        }

        return $query;
    }
}
