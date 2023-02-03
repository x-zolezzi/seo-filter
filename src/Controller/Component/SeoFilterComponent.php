<?php

namespace SeoFilter\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Datasource\Exception\PageOutOfBoundsException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\RedirectException;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\Query;
use Cake\Routing\Router;
use Cake\Utility\Inflector;

class SeoFilterComponent extends Component
{

    protected $components = ['Paginator'];

    private array $paginate = [
        'enabled' => false,
        'items_per_page' => 0,
    ];

    protected array $criteres = []; // QueryBuilder
    protected array $criteresFilter = []; //
    protected ?int $page = null;

    protected array $order = []; // paramètres d'ordre de la requête

    private bool $countResults = true;

    private array $config = [];

    private ?string $mainModel = null;

    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        if(isset($config['paginate']))
            $this->paginate = [
                'enabled' => $config['paginate']['enabled'],
                'maxLimit' => $config['paginate']['maxLimit'] > 0 ? $config['paginate']['maxLimit'] : 1,
                'limit' => $config['paginate']['limit'] > 0 ? $config['paginate']['limit'] : 1
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
        $this->_setPage();
        parent::initialize($config);
    }

    /**
     * Permet de déterminer sur quel Modèle filtrer la requête finale
     * @param string $model
     * @return $this
     */
    public function setMainModel(string $model): self{
        $this->mainModel = $model;
        return $this;
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

    private function _getStdMatching(
        &$matchings,
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

        $matchings[$critere->model][] = [$critere->model . '.' . $critere->colonne . $operateur => $filterValue];
    }

    private function _getStdInnerJoinWiths(
        &$joins,
        $filterValue,
        $filterKey,
        $critere
    ): void{
        if(!is_array($filterValue)) {
            $filterValue = [$filterValue];
        }

        foreach ($filterValue as $value){
            $operateur = ' IN';
            $query = (new TableLocator())->get($critere->association_model)->find()
                ->select(Inflector::singularize(Inflector::underscore($this->mainModel)) . '_id')
                ->where([
                    $critere->association_model . '.' . Inflector::singularize(Inflector::underscore($critere->model)) . '_id' => $value
                ]);
            $joins[$critere->model][] = [$this->mainModel . '.' . $critere->colonne . $operateur => $query];
        }
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
            $filters = $this->getController()->getRequest()->getData('seofilter_filters_criteres') ?? [];
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

            if(is_string($filterValue) && strpos($filterValue, ' ')){
                $filterValue = explode(' ', $filterValue);
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
        $matchings = [];
        foreach ($this->criteres as $critere){
            if($critere->method !== 'MATCHING'){
                continue;
            }

            $filterValue = $this->criteresFilter[$critere->slug];
            $filterKey = $critere->slug;

            if($critere->critere_type === 'CHECKBOX'){
                $this->_getStdMatching($matchings, $filterValue, $filterKey, $critere);
            }
        }

        return $matchings;
    }

    public function applyInnerJoins(): array{
        $joins = [];
        foreach ($this->criteres as $critere){
            if($critere->method !== 'INNERJOINWITH'){
                continue;
            }

            $filterValue = $this->criteresFilter[$critere->slug];
            $filterKey = $critere->slug;

            if($critere->critere_type === 'CHECKBOX'){
                $this->_getStdInnerJoinWiths($joins, $filterValue, $filterKey, $critere);
            }
        }

        // [this->mainModel.id in => foreach critere
        return $joins;
    }

    public function getConditions($forceRedirect = true, string $method = 'WHERE')
    {
        // TODO: mettre dans le app.php
        if ($forceRedirect) {
            //Controle ordre des critères => Redirection 301
            $url = Router::url(null, true);
            $validUrl = $this->getUrl($this->getController()->getRequest()->getParam('slug_seo_filter'));
            if ($validUrl != $url) {
                // TODO: mettre nom route dans app.php
                $this->getController()->log('Redirect: from ' . $url . ' to ' . $validUrl);
                throw new RedirectException($validUrl, 301);
            }
        }

        switch ($method){
            case  'WHERE':
                return $this->applyConditions();

            case 'MATCHING':
                return $this->applyMatching();

            case 'INNERJOINWITH':
                return $this->applyInnerJoins();
        }
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

        // Permet d'exprimer sur quelle table il faut appliquer un distinct
        $this->setMainModel($filtre->model);

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

    public function getCriteresPourUrl(): string{
        ksort($this->criteresFilter);
        $criteresPourUrl = $this->criteresFilter;

        $str = '';
        foreach ($criteresPourUrl as $k => $critere){
            if(is_string($critere) && strpos($critere, ' ') !== false){
                $critere = explode(' ', $critere);
            }

            if(is_array($critere)){
                $criteresPourUrl[$k] = implode('+', $critere);
            }

            $str .= $k . '-' . $criteresPourUrl[$k] . '/';
        }

        return $str;
    }

    public function getUrl($filtre_slug, $full_url = true)
    {
        $queryParams = [];
        $str = $this->getCriteresPourUrl();
        if ($full_url) {
            return Router::url(['_name' => $filtre_slug . (empty($str) ? '_empty' : ''), 'filtres' => $str, 'slug_seo_filter' => $filtre_slug, '?' => $this->getController()->getRequest()->getQuery()], true);
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

    private function _setPage(): self{
        if($this->config['paginate']['enabled']){
            $this->Paginator->setConfig($this->paginate);
            $this->page = $this->getController()->getRequest()->getData('page') ?? $this->getController()->getRequest()->getQuery('page') ?? 1;
            $this->paginate['page'] = $this->page;
        }

        return $this;
    }

    public function applyFilters(Query &$query): Query{

        $forceRedirect = !$this->getController()->getRequest()->is('ajax');

        $conditions = $this->getConditions($forceRedirect, 'WHERE');
        $query->andWhere($conditions);

        $matchings = $this->getConditions($forceRedirect, 'MATCHING');
        foreach ($matchings as $model => $conditions){
            $query->matching($model, function (Query $q) use($conditions): Query{
                return $q->where($conditions);
            });
        }

        $joins = $this->getConditions($forceRedirect, 'INNERJOINWITH');
        foreach ($joins as $model => $conditions){
            $query->where($conditions);
        }

        if(!empty($this->order)){
            $query->order($this->order);
        }

        if($this->page){
            try{
                $this->Paginator->paginate($query, $this->paginate);
            }catch (NotFoundException $exception){
                $lastPage = $this->Paginator->getPagingParams()[$this->mainModel]['pageCount'];
                $url = $this->getUrl($this->getController()->getRequest()->getParam('slug_seo_filter'));
                $query = array_merge($this->getController()->getRequest()->getQuery(), ['page' => $lastPage]);
                throw new RedirectException(Router::url(['url' => $url, '?' => $query]), 302);
            }
        }

        if($this->mainModel){
            $query->distinct($this->mainModel . '.id');
        }

        return $query;
    }
}
