# seo-filter
Filtre à facette avec pagination pour CakePHP 4

## Installation
Charger le plugin dans le fichier *src/Application.php*
```shell
php bin/cake.php plugin load SeoFilter
```

Créer les tables nécessaires à l'aide des requêtes suivantes:

```sql
CREATE TABLE IF NOT EXISTS `seofilter_filters` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `controller` varchar(255) NOT NULL,
    `action` varchar(255) NOT NULL,
    `model` varchar(255) NOT NULL,
    `function_find` varchar(255) NOT NULL,
    `element` varchar(255) NOT NULL,
    `titre` varchar(255) DEFAULT NULL,
    `seo_titre` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
    `seo_description` varchar(370) CHARACTER SET latin1 DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `created` datetime NOT NULL,
    `modified` datetime NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `seofilter_filters_criteres` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `seofilter_filter_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `model` varchar(255) NOT NULL,
    `colonne` varchar(255) NOT NULL,
    `colonne_label` varchar(255) NOT NULL,
    `function_find_values` varchar(255) NOT NULL,
    `ordre` tinyint(1) unsigned NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `created` datetime NOT NULL,
    `modified` datetime NOT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `seofilter_filter_id_slug` (`seofilter_filter_id`,`slug`),
    CONSTRAINT `FK_seofilter_filters_criteres_seofilter_filters` FOREIGN KEY (`seofilter_filter_id`) REFERENCES `seofilter_filters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE IF NOT EXISTS `seofilter_filters_orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `seofilter_filter_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `model` varchar(255) NOT NULL,
    `colonne` varchar(255) NOT NULL,
    `direction` enum('ASC','DESC') NOT NULL,
    `ordre` tinyint(1) unsigned NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `created` datetime NOT NULL,
    `modified` datetime NOT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    KEY `seofilter_filter_id` (`seofilter_filter_id`),
    CONSTRAINT `FK_seofilter_filters_orders_seofilter_filters` FOREIGN KEY (`seofilter_filter_id`) REFERENCES `seofilter_filters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE IF NOT EXISTS `seofilter_filters_urls` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `seofilter_filter_id` int(11) NOT NULL,
    `seo_url` varchar(255) NOT NULL,
    `seo_titre` varchar(255) NOT NULL,
    `seo_description` varchar(255) NOT NULL,
    `description` text,
    `created` datetime NOT NULL,
    `modified` datetime NOT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `seofilter_filter_id_seo_url` (`seofilter_filter_id`,`seo_url`) USING BTREE,
    CONSTRAINT `FK_seofilter_filters_urls_seofilter_filters` FOREIGN KEY (`seofilter_filter_id`) REFERENCES `seofilter_filters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

Mettre à jour le fichier config/app.php
```php
[
    // ...
    'SeoFilter' => [
        'config' => [
            'paginate' => [
                'enabled' => true,
                'items_per_page' => 10
            ],
            'countResults' => true, // Si activé, renvoie le nombre de résultats après application du filtre
            'previewResults' => false // Si activé, affiche le nombre de résultats après application d'un critère après ceux-ci
        ]
    ]
]
```

### Créer un filtre
Tout d'abord, il faut créer un filtre sur lequel le plugin est capable de travailler. Rendez-vous dans la table `seofilter_filters` et créez votre premier filtre.
Par exemple:
```sql
INSERT INTO `seofilter_filters` (`name`, `slug`, `controller`, `action`, `model`, `function_find`, `element`, `titre`, `seo_titre`, `seo_description`, `is_active`, `created`, `modified`) VALUES ('Produits', 'categories', 'Categories', 'details', 'Produits', 'findItemsInParentCategory', 'carte_produit', 'Les produits', 'lorem', 'lorem', 1, NOW(), NOW());
```

Cette ligne indique au plugin que, sur l'url */categories*, il peut y avoir un filtre qui suit.
La colonne *model* et la clonne *function_find* indiquent la méthode appelée pour récupérer les données du filtre.
```php
// src/Model/Table/ProduitsTable.php
public function findItemsInParentCategory(array $conditions = [], array $order = []): Query{
     // Récupération des données...
     // Penser à intégrer les paramètres $conditions et $order à votre requête.
}
```

Et dans le controller:
```php
// src/Controller/CategoriesController.php
public function details(){
    $this->loadComponent('SeoFilter.SeoFilter');

    // Récupérer les conditions passées dans l'URL
    $conditionsFilter = $this->SeoFilter->getConditions();

    // Récupération des données avec la méthode définie
    $results = $this->fetchTable('Produits')->findItemsInParentCategory($conditionsFilter);

    $this->set('items', $results->all());
}
```

Dans la vue, vous pouvez afficher les résultats grâce au même élément que celui inséré en base de données.
```php
// Dans une vue
<?php foreach ($items as $i => $item): ?>
    <?= $this->element('carte_produit', ['item' => $item]) ?>
<?php endforeach; ?>
```

### Créer un critère de filtre
Afin de pouvoir filtrer les éléments sur notre filtre, il faut lui fournir des critères.
Commencez par éxécuter la requête suivante:
```sql
INSERT INTO `seofilter_filters_criteres` (`seofilter_filter_id`, `name`, `slug`, `model`, `colonne`, `colonne_label`, `function_find_values`, `ordre`, `is_active`, `created`, `modified`) VALUES (1, 'Catégorie', 'categorie', 'Categories', 'slug', 'titre', 'findValuesForFilter', 1, 1, NOW(), NOW());
```

Cette ligne indique que pour le filtre crée, on ajoute un critère *Catégorie*.
Pour trier sur ce critère, il faut passer un slug *categorie-*, suivi des paramètres que l'on veut.
Les paramètres qui suivent seront pris dans le model *Categories*, dans la clonne *slug*. (Ces données dont l'url sont
retournées grâce à l'appel à SeoFilter::getConditions() dans le controller)

Afin de créer ces filtres dynamiquement sur la vue, on peut passer une méthode du modèle dans la
colonne *function_find_values*, dans notre exemple:
```php
// src/Model/Table/CategoriesTable.php
public function findValuesForFilter(): Query{
    return $this->find()->where(['Categories.is_active' => true])->order(['Categories.ordre' => 'ASC']);
}
```

```php
// src/Controller/CategoriesController.php
public function details(){
    // ...

    // Construction des filtres
    $this->SeoFilter->getFilterData();
}
```

Et dans la vue:
```php
foreach ($seo_filter->seofilter_filters_criteres as $seofilter_filters_criteres) {
    echo $seofilter_filters_criteres->name // Nom du critère
    $slug = $seofilter_filters_criteres->slug;
    if (isset($values_for_seofilter_filters_criteres[$seofilter_filters_criteres->id])) {
        $filtre_criteres = $values_for_seofilter_filters_criteres[$seofilter_filters_criteres->id];
        foreach ($filtre_criteres as $filtre_critere) {
            $value_input = $filtre_critere->{$seofilter_filters_criteres->colonne};
            $label_input = $filtre_critere->{$seofilter_filters_criteres->colonne_label};

            $checked = false;
            foreach ($seo_filter_criteres_values as $value) {
                if ($value['slug'] == $slug) {
                    if (!is_array($value['value'])) {
                        $value['value'] = [$value['value']];
                    }
                    foreach ($value['value'] as $value_selected) {
                        if ($value_selected == $value_input) {
                            $checked = true;
                            break;
                        }
                    }
                }
            }
            ?>
            <input <?= ($checked ? 'checked="checked"' : '') ?> class="" name="seofilter_filters_criteres[<?= $slug ?>][]" value="<?= $value_input ?>" type="checkbox" id="seofilter_filters_criteres-<?= $slug ?>-<?= $value_input ?>">
            <label for="seofilter_filters_criteres-<?= $slug ?>-<?= $value_input ?>"><?= $label_input ?></label>
        }
    }
}
```

### Rechargement Ajax
**Afin de faciliter le traitement Ajax, il est recommandé de mettre tous les critères et ordres dans un formulaire**

```js
$(function() {
    $('#filtres input, #filtres select').on('change', function() {
        var data_filter = $('#filtres').serializeArray();
        $.post(
            '<?= $this->Url->build('/seo-filter/render/' . $seo_filter->slug) ?>',
            data_filter,
            function(data) {
                window.history.pushState("", "", data.url);
                $('#js-products-container').html(data.html);
            }
        );
    });
});
```

# TODO LIST
1. Système de pagination lié au component `Paginator` de CakePHP
2. Permettre ou non l'affichage entre parenthèses du nombre de résultats après application d'un filtre
3. Possibilité de passer plusieurs paramètres aux éléments de rendu
