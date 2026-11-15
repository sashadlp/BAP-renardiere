<?php

// 1. Chargement de la feuille de style compilée par Sass dans assets/css/
function mon_theme_fse_assets()
{
    $css_file = get_template_directory() . '/assets/css/style.css';
    $css_uri  = get_template_directory_uri() . '/assets/css/style.css';


    wp_enqueue_style(
        'mon-theme-fse-style',
        $css_uri,
        [],
        file_exists($css_file) ? filemtime($css_file) : wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'mon_theme_fse_assets');


function mon_theme_fse_setup()
{
    add_theme_support('post-thumbnails');
    add_theme_support('editor-styles');

    // Charge les styles Sass également dans l'éditeur
    if (file_exists(get_template_directory() . '/assets/css/style.css')) {
        add_editor_style('assets/css/style.css');
    } elseif (file_exists(get_template_directory() . '/assets/css/stye.css')) {
        add_editor_style('assets/css/stye.css');
    }
}
add_action('after_setup_theme', 'mon_theme_fse_setup');


function mon_theme_fse_activation()
{
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'mon_theme_fse_activation');

function mon_theme_fse_desactivation()
{
    flush_rewrite_rules();
}
add_action('switch_theme', 'mon_theme_fse_desactivation');

// 4. Déclaration du Custom Post Type "Projet"
function mon_theme_fse_projets_cpt()
{
    register_post_type('projet', [
        'labels' => [
            'name'          => 'Projets',
            'singular_name' => 'Projet',
            'add_new_item'  => 'Ajouter un projet',
        ],
        'public'       => true,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-portfolio',
        'supports'     => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'projets'],
    ]);
}
add_action('init', 'mon_theme_fse_projets_cpt');

// 5. Déclaration de la taxonomie "Technologies"
function mon_theme_fse_technologies_taxonomy()
{
    // Création de la taxonomie "Technologies" pour le type de contenu "projet"
    register_taxonomy('technologie', 'projet', [
        'labels' => [
            'name' => 'Technologies',
            'singular_name' => 'Technologie',
            'search_items' => 'Rechercher une technologie',
            'all_items' => 'Toutes les technologies',
            'edit_item' => 'Modifier une technologie',
            'update_item' => 'Mettre à jour une technologie',
            'add_new_item' => 'Ajouter une technologie',
            'new_item_name' => 'Nouvelle technologie',
            'not_found' => 'Aucune technologie trouvée',
            'menu_name' => 'Technologies',
        ],
        'public' => true,
        'hierarchical' => true, // Les termes sont affichés comme des étiquettes et non des cases à cocher
        'show_admin_column' => true, // Affiche la colonne dans la liste des projets
        'show_in_rest' => true, // Obligatoire pour l'éditeur de blocs
        'rewrite' => ['slug' => 'technologie'],
    ]);
}
add_action('init', 'mon_theme_fse_technologies_taxonomy');

// 6. Déclaration des métadonnées personnalisées pour les projets
function mon_theme_fse_projets_meta()
{
    register_post_meta('projet', 'client', [
        'type'              => 'string',
        'single'            => true,
        'show_in_rest'      => true,
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    register_post_meta('projet', 'annee', [
        'type'              => 'string',
        'single'            => true,
        'show_in_rest'      => true,
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    register_post_meta('projet', 'lien_projet', [
        'type'              => 'string',
        'single'            => true,
        'show_in_rest'      => true,
        'sanitize_callback' => 'esc_url_raw',
    ]);
}
add_action('init', 'mon_theme_fse_projets_meta');

// 7. Compteur de visites pour les projets
function mon_theme_fse_compter_vues()
{
    if (!is_singular('projet')) {
        return;
    }

    $post_id = get_queried_object_id();
    $vues    = intval(get_post_meta($post_id, '_vues', true));
    update_post_meta($post_id, '_vues', $vues + 1);
}
add_action('template_redirect', 'mon_theme_fse_compter_vues');

function mon_theme_fse_vues_valeur($source_args, $block_instance)
{
    $post_id = $block_instance->context['postId'] ?? get_the_ID();
    $vues    = intval(get_post_meta($post_id, '_vues', true));

    return $vues . ($vues > 1 ? ' vues' : ' vue');
}

function mon_theme_fse_bindings()
{
    register_block_bindings_source('mon-theme-fse/vues', [
        'label'              => 'Nombre de vues',
        'get_value_callback' => 'mon_theme_fse_vues_valeur',
        'uses_context'       => ['postId'],
    ]);
}
add_action('init', 'mon_theme_fse_bindings');

function mon_theme_fse_colonne_vues($columns)
{
    $columns['vues'] = 'Visites';
    return $columns;
}
add_filter('manage_projet_posts_columns', 'mon_theme_fse_colonne_vues');

function mon_theme_fse_colonne_vues_contenu($column, $post_id)
{
    if ($column === 'vues') {
        echo intval(get_post_meta($post_id, '_vues', true));
    }
}
add_action('manage_projet_posts_custom_column', 'mon_theme_fse_colonne_vues_contenu', 10, 2);