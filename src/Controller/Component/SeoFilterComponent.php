<?php

namespace SeoFilter\Controller\Component;

use Cake\Controller\Component;
use Cake\Http\Exception\RedirectException;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Router;

class SeoFilterComponent extends Component
{



    public function getConditions($filtres = null, $slug_seo_filter = null, $forceRedirect = true)
    {
        $conditions = [];

        $filtresParam = $filtres ?? $this->getController()->getRequest()->getParam('filtres');
        if (!empty($filtresParam)) {
            $filters = explode('/', $filtresParam);
            $filtre_slug = $slug_seo_filter ?? $this->getController()->getRequest()->getParam('slug_seo_filter');
            if (!empty($filtre_slug)) {
                $this->getController()->loadModel('SeoFilter.SeofilterFilters');
                $filtre = $this->getController()->SeofilterFilters->find()->where(['SeofilterFilters.slug' => $filtre_slug, 'SeofilterFilters.is_active' => true])->first();
                if (!empty($filtre)) {
                    if (!empty($filters)) {
                        $criteres = [];
                        foreach ($filters as $filter) {
                            $explode = explode('-', $filter, 2);
                            if (!isset($explode[1])) {
                                continue;
                            }
                            $filterKey = $explode[0];
                            $filterValue = $explode[1];

                            $this->getController()->loadModel('SeoFilter.SeofilterFiltersCriteres');
                            $critere = $this->getController()->SeofilterFiltersCriteres->find()->where(['SeofilterFiltersCriteres.seofilter_filter_id' => $filtre->id, 'SeofilterFiltersCriteres.slug' => $filterKey, 'SeofilterFiltersCriteres.is_active' => true])->first();

                            if (!empty($critere)) {
                                $criteres[$critere->id] = $critere->slug . '-' . $filterValue;

                                $operateur = '';
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
                                } elseif (strpos($filterValue, ' ') !== false) {
                                    $operateur = ' IN';
                                    $filterValue = explode(' ', $filterValue);
                                } elseif (strpos($filterValue, '+') !== false) {
                                    $operateur = ' IN';
                                    $filterValue = explode('+', $filterValue);
                                }
                                $conditions[$critere->model . '.' . $critere->colonne . $operateur] = $filterValue;
                            } else {
                                // Critere non trouvé => ?
                            }
                        }
                        if ($forceRedirect) {
                            //Controle ordre des critères => Redirection 301
                            $url = $this->getController()->getRequest()->getUri()->base . $this->getController()->getRequest()->getUri()->getPath();
                            if ($this->getUrl($criteres, $filtre_slug) != $url) {
                                throw new RedirectException(Router::url(['_name' => 'supercars', 'filtres' => $implode, 'slug_seo_filter' => $filtre_slug]), 301);
                            }
                        }
                    }
                }
            }
        }
        return $conditions;
    }

    public function getCriteresValues()
    {
        $criteres = [];
        $filtresParam = $filtres ?? $this->getController()->getRequest()->getParam('filtres');
        if (!empty($filtresParam)) {
            $filters = explode('/', $filtresParam);
            $filtre_slug = $slug_seo_filter ?? $this->getController()->getRequest()->getParam('slug_seo_filter');
            if (!empty($filtre_slug) && !empty($filters)) {
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
            }
        }
        return $criteres;
    }

    public function getFilterData()
    {
        $this->getController()->loadModel('SeoFilter.SeofilterFilters');
        $filtre = $this->getController()->SeofilterFilters->find()->contain(['SeofilterFiltersCriteres', 'SeofilterFiltersOrders'])->where(['SeofilterFilters.slug' => $this->getController()->getRequest()->getParam('slug_seo_filter'), 'SeofilterFilters.is_active' => true])->first();
        $this->getController()->set('seo_filter', $filtre);

        $values_for_seofilter_filters_criteres = [];
        foreach ($filtre->seofilter_filters_criteres as $seofilter_filters_criteres) {
            $this->getController()->loadModel($seofilter_filters_criteres->model);
            $values_for_seofilter_filters_criteres[$seofilter_filters_criteres->id] = $this->getController()->{$seofilter_filters_criteres->model}->{$seofilter_filters_criteres->function_find_values}();
        }
        $this->getController()->set('values_for_seofilter_filters_criteres', $values_for_seofilter_filters_criteres);

        $seo_filter_criteres_values = $this->getCriteresValues();
        $this->getController()->set('seo_filter_criteres_values', $seo_filter_criteres_values);

        if (!empty($this->getController()->getRequest()->getParam('filtres'))) {
            $this->getController()->loadModel('SeoFilter.SeofilterFiltersUrls');
            $seo_filter_urls = $this->getController()->SeofilterFiltersUrls->find()->where(['SeofilterFiltersUrls.seo_url' => $this->getController()->getRequest()->getParam('filtres'), 'SeofilterFiltersUrls.seofilter_filter_id' => $filtre->id])->first();
            $this->getController()->set('seo_filter_urls', $seo_filter_urls);
        }
    }

    public function getUrl($criteres, $filtre_slug, $full_url = true)
    {
        ksort($criteres);
        $implode = str_replace(' ', '+', implode('/', $criteres));
        if ($full_url) {
            $order = [];
            if (!empty($this->getOrder())) {
                $order = ['order' => $this->getController()->getRequest()->getData('order')];
            }
            return Router::url(['_name' => $filtre_slug . (empty($implode) ? '_empty' : ''), 'filtres' => $implode, 'slug_seo_filter' => $filtre_slug, '?' => $order]);
        } else {
            return '/' . $implode;
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

    public function getOrder()
    {
        $orderParam = $this->getController()->getRequest()->getData('order');
        $order = [];
        if (!empty($orderParam)) {
            $explode = explode('-', $orderParam);
            if (count($explode) == 2 && in_array(strtoupper($explode[1]), ['ASC', 'DESC'])) {
                $order[$explode[0]] = strtoupper($explode[1]);
            }
        }
        return $order;
    }
}