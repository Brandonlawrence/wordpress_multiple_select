<?php

/**
 * @package WoocommerceComboProduct
 */

class ComboProductHelperFunctions
{

    public static function get_combo_product_tag($productId)
    {
        $tag = get_post_meta($productId, '_combo_product_tag');
        if (is_array($tag)) {
            if (!empty($tag)) {
                return $tag[0];
            }

        }
        return false;

    }

    public static function get_combo_product_tag_slug($productId)
    {
        $selectedTag = ComboProductHelperFunctions::get_combo_product_tag($productId);
        $tagSlug = false;
        if ($selectedTag) {
            $allTags = get_terms('product_tag');

            if (!empty($allTags) && !is_wp_error($allTags)) {
                foreach ($allTags as $tag) {
                    if ($tag->name == $selectedTag) {
                        $tagSlug = $tag->slug;
                    }
                }
            }

        }
        return $tagSlug;
    }

    public static function get_data_for_combo_child_products($productId)
    {
        $selectedTagSlug = ComboProductHelperFunctions::get_combo_product_tag_slug($productId);
        $childProducts = [];
        $args = [
            'tag' => $selectedTagSlug,
        ];
        if ($selectedTagSlug) {

            $tagged_products = wc_get_products($args);

            foreach ($tagged_products as $tagged_product) {

                $childProducts[] = ["name" => $tagged_product->get_name(), 'inStock' => $tagged_product->is_in_stock()];
            }
        }
        return $childProducts;
    }

    public static function get_custom_properties_for_variations($variations)
    {
        $variation_custom_properties = [];
        if ($variations) {
            foreach ($variations as $variation) {
                $id = $variation['variation_id'];
                $variation_custom_properties[$id] = get_post_meta($id, '_child_product_count', true);
            }
        }
        return $variation_custom_properties;
    }

    public static function get_all_products_tags()
    {
        $terms = get_terms('product_tag');
        $term_array = array();
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $term_array[] = $term->name;
            }
        }
        return $term_array;
    }

    public static function get_data_for_all_products_with_tags()
    {

        $allTags = ComboProductHelperFunctions::get_all_products_tags();
        $taggedProductsWithData = [];
        if (!empty($allTags)) {

            $args = [
                'limit' => 10000,
                'tag' => $allTags,
            ];
            $tagged_products = wc_get_products($args);

            if (!empty($tagged_products)) {

                foreach ($tagged_products as $tagged_product) {
                    $productTagsRaw = wp_get_post_terms($tagged_product->get_id(), 'product_tag');

                    $productTags = [];

                    foreach ($productTagsRaw as $productTag) {
                        $productTags[] = $productTag->name;
                    }

                    $taggedProductsWithData[] = ['name' => $tagged_product->get_name(), 'inStock' => $tagged_product->is_in_stock(), 'tags' => $productTags];
                }

            }

        }
        return $taggedProductsWithData;

    }

}
