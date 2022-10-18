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
# TODO LIST
1. Système de pagination lié au component `Paginator` de CakePHP
2. Permettre ou non l'affichage entre parenthèses du nombre de résultats après application d'un filtre
