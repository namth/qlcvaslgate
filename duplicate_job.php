<?php
/* 
    Template Name: Duplicate Job
*/
if (isset($_GET['jobid'])  && ($_GET['jobid'] != "")) {
    $postid         = $_GET['jobid'];
    $current_post   = get_post($postid);
    $partner_2      = get_field('partner_2', $postid);

    # create new post
    $args = array(
        'post_title'    => "Bản sao của " . get_the_title($postid),
        'post_content'  => get_the_content($postid),
        'post_status'   => 'publish',
        'post_type'     => 'job',
    );

    $error = false;
    $inserted = wp_insert_post($args, $error);

    # copy all nguồn đầu việc
    $terms      = get_the_terms($postid, 'post_tag');
    $term_names = wp_list_pluck($terms, 'name');
    wp_set_object_terms($inserted, $term_names, 'post_tag');
    
    # copy all agency
    $terms      = get_the_terms($postid, 'agency');
    $term_names = wp_list_pluck($terms, 'name');
    wp_set_object_terms($inserted, $term_names, 'agency');
    
    # copy all taxonomy group
    $terms      = get_the_terms($postid, 'group');
    $term_names = wp_list_pluck($terms, 'name');
    wp_set_object_terms($inserted, $term_names, 'group');

    # update all fields you need
    # get all fields
    $all_fields = get_field_objects($postid);

    // print_r($all_fields);

    echo "<table>";
    foreach ($all_fields as $single_field) {
        $key = $single_field["key"];

        echo "<tr>
            <td>" . $key . "</td>
            <td>" . $single_field["name"] . "</td>
            <td>" . $single_field["type"] . "</td>";
        
        switch ($single_field["type"]) {
            case 'post_object':
                $value = $single_field["value"]->ID;
                break;
            
            case 'user':
                if ($single_field["value"]) {
                    $value = $single_field["value"]["ID"];
                } else $value = "";
                break;

            case 'repeater':
                break;
            
            default:
                $value = $single_field["value"];
                break;
        }
        echo "<td>" . $value . "</td>";
        update_field($key, $value, $inserted);
        
        echo "</tr>";
    }
    echo "</table>";

    # caculate new REF
    foreach ($term_names as $term_name) {
        if ($term_name != "Tiềm năng") {
            $terms = get_term_by('name', $term_name, 'group');
            $term_id    = $terms->term_id;
            $groups_code = get_field('groups_code', 'term_' . $term_id);
            $order_number = get_field('order_number', 'term_' . $term_id);
            $partner_code = get_field('partner_code', 'user_' . $partner_2["ID"]);

            $order_number++;
            $our_ref = $groups_code . $order_number . $partner_code;

            # tự động tăng số thứ tự trong group đó
            update_field('order_number', $order_number, 'term_' . $term_id);

            break;
        }
    }
    # set the new ref
    update_field('field_6099f75a87258', $our_ref, $inserted); # Số REF của mình

    wp_redirect(get_permalink($inserted));
    exit;
}