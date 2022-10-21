<?php

namespace SeoFilter\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use SeoFilter\Controller\AppController;

class RenderController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        $anonymousActions = [
            'index',
        ];
        $this->Authentication->addUnauthenticatedActions($anonymousActions);

        $this->loadComponent('SeoFilter.SeoFilter', Configure::read('SeoFilter.config'));
        
        $this->loadComponent('SeoFilter.SeoFilter');

        parent::beforeFilter($event);
    }

    public function index()
    {
        $this->loadModel('SeoFilter.SeofilterFilters');
        $filtre = $this->SeofilterFilters->find()->where(['SeofilterFilters.slug' => $this->getRequest()->getParam('slug_seo_filter'), 'SeofilterFilters.is_active' => true])->first();
        $this->set('filtre', $filtre);

        $criteres = $this->SeoFilter->getCriteres($this->getRequest()->getData('seofilter_filters_criteres'));

        $url = $this->SeoFilter->getUrl($criteres, $this->getRequest()->getParam('slug_seo_filter'), true);

        $conditionsFilter = $this->SeoFilter->getConditions($this->SeoFilter->getUrl($criteres, $this->getRequest()->getParam('slug_seo_filter'), false), $this->getRequest()->getParam('slug_seo_filter'), false);

        $order = $this->SeoFilter->getOrder();

        $this->loadModel($filtre->model);
        $items = $this->{$filtre->model}->{$filtre->function_find}($conditionsFilter, $order);
        $this->set('items', $items);

        $html = $this->render('render')->getBody()->__toString();
        
        $data = [
            'url' => $url,
            'html' => $html
        ];

        if($this->SeoFilter->getConfig('countResults')){
            $data['total_items'] = $items->count();
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode($data));
    }
}
