<?php
/*
    Template Name: Cập nhật taxonomy cho bài viết
*/
$history_link = $_SERVER['HTTP_REFERER'];
$thongbao = "";
$posts_found = array();
$search_performed = false;

// Process form submission for searching posts
if (
    is_user_logged_in() &&
    isset($_POST['qlcv_search_nonce_field']) &&
    wp_verify_nonce($_POST['qlcv_search_nonce_field'], 'qlcv_search_nonce')
) {
    $post_type = sanitize_text_field($_POST['qlcv_post_type']);
    $taxonomy = sanitize_text_field($_POST['qlcv_taxonomy']);
    $term_id = intval($_POST['qlcv_current_term_id']);
    
    if (!empty($post_type) && !empty($taxonomy) && $term_id > 0) {
        // Get posts by post type and taxonomy term
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term_id,
                ),
            ),
        );
        
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            $posts_found = $query->posts;
            $search_performed = true;
        } else {
            $thongbao = '<div class="alert alert-warning">' . __('Không tìm thấy bài viết nào với điều kiện tìm kiếm', 'qlcv') . '</div>';
        }
        wp_reset_postdata();
    } else {
        $thongbao = '<div class="alert alert-danger">' . __('Vui lòng điền đầy đủ thông tin tìm kiếm', 'qlcv') . '</div>';
    }
}

// Process form submission for updating taxonomy
if (
    is_user_logged_in() &&
    isset($_POST['qlcv_update_nonce_field']) &&
    wp_verify_nonce($_POST['qlcv_update_nonce_field'], 'qlcv_update_nonce')
) {
    $post_ids = $_POST['qlcv_post_ids'];
    $taxonomy = sanitize_text_field($_POST['qlcv_taxonomy']);
    $new_term_id = intval($_POST['qlcv_new_term_id']);
    $old_term_id = intval($_POST['qlcv_old_term_id']);
    
    if (!empty($post_ids) && !empty($taxonomy) && $new_term_id > 0) {
        $updated_count = 0;
        $errors = array();
        
        foreach ($post_ids as $post_id) {
            $post_id = intval($post_id);
            
            // Remove old term and add new term
            $remove_result = wp_remove_object_terms($post_id, $old_term_id, $taxonomy);
            $add_result = wp_set_object_terms($post_id, $new_term_id, $taxonomy, true);
            
            if (!is_wp_error($remove_result) && !is_wp_error($add_result)) {
                $updated_count++;
            } else {
                $errors[] = sprintf(__('Lỗi cập nhật bài viết ID %d', 'qlcv'), $post_id);
            }
        }
        
        if ($updated_count > 0) {
            $thongbao = '<div class="alert alert-success">' . 
                sprintf(__('Đã cập nhật thành công %d bài viết', 'qlcv'), $updated_count) . 
                '</div>';
        }
        
        if (!empty($errors)) {
            $thongbao .= '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
        }
        
        if ($updated_count === 0 && empty($errors)) {
            $thongbao = '<div class="alert alert-danger">' . __('Không có bài viết nào được cập nhật', 'qlcv') . '</div>';
        }
    } else {
        $thongbao = '<div class="alert alert-danger">' . __('Vui lòng điền đầy đủ thông tin cập nhật', 'qlcv') . '</div>';
    }
}

get_header();
get_sidebar();
?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <!-- Page Heading Start -->
        <div class="col-12 col-lg-12 mb-20">
            <div class="page-heading">
                <h3 class="title"><?php _e('Cập nhật taxonomy cho bài viết', 'qlcv'); ?></h3>
            </div>
        </div><!-- Page Heading End -->

        <div class="col-12 mb-30">
            <div class="box">
                <div class="box-body">
                    <?php
                    if ($thongbao) {
                        echo $thongbao;
                    }
                    ?>
                    
                    <!-- Search Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><?php _e('Tìm kiếm bài viết', 'qlcv'); ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="#">
                                <?php wp_nonce_field('qlcv_search_nonce', 'qlcv_search_nonce_field'); ?>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="qlcv_post_type"><?php _e('Post Type', 'qlcv'); ?> *</label>
                                            <input type="text" class="form-control" id="qlcv_post_type" name="qlcv_post_type" 
                                                   placeholder="<?php _e('Ví dụ: product, event, news', 'qlcv'); ?>" 
                                                   value="<?php echo isset($_POST['qlcv_post_type']) ? esc_attr($_POST['qlcv_post_type']) : ''; ?>" required>
                                            <small class="form-text text-muted"><?php _e('Nhập tên post type cần tìm kiếm', 'qlcv'); ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="qlcv_taxonomy"><?php _e('Taxonomy', 'qlcv'); ?> *</label>
                                            <input type="text" class="form-control" id="qlcv_taxonomy" name="qlcv_taxonomy" 
                                                   placeholder="<?php _e('Ví dụ: product_category, event_type', 'qlcv'); ?>" 
                                                   value="<?php echo isset($_POST['qlcv_taxonomy']) ? esc_attr($_POST['qlcv_taxonomy']) : ''; ?>" required>
                                            <small class="form-text text-muted"><?php _e('Nhập tên taxonomy hiện tại', 'qlcv'); ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="qlcv_current_term_id"><?php _e('ID Taxonomy hiện tại', 'qlcv'); ?> *</label>
                                            <input type="number" class="form-control" id="qlcv_current_term_id" name="qlcv_current_term_id" 
                                                   placeholder="<?php _e('Ví dụ: 5, 10, 25', 'qlcv'); ?>" 
                                                   value="<?php echo isset($_POST['qlcv_current_term_id']) ? esc_attr($_POST['qlcv_current_term_id']) : ''; ?>" required>
                                            <small class="form-text text-muted"><?php _e('Nhập ID của term hiện tại', 'qlcv'); ?></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-search"></i> <?php _e('Tìm kiếm bài viết', 'qlcv'); ?>
                                </button>
                            </form>
                        </div>
                    </div>

                    <?php if ($search_performed && !empty($posts_found)): ?>
                    <!-- Results and Update Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5><?php _e('Kết quả tìm kiếm và cập nhật taxonomy', 'qlcv'); ?></h5>
                            <p class="mb-0"><?php printf(__('Tìm thấy %d bài viết', 'qlcv'), count($posts_found)); ?></p>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <?php wp_nonce_field('qlcv_update_nonce', 'qlcv_update_nonce_field'); ?>
                                
                                <!-- Hidden fields to preserve search data -->
                                <input type="hidden" name="qlcv_taxonomy" value="<?php echo esc_attr($_POST['qlcv_taxonomy']); ?>">
                                <input type="hidden" name="qlcv_old_term_id" value="<?php echo esc_attr($_POST['qlcv_current_term_id']); ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="qlcv_new_term_id"><?php _e('ID Taxonomy mới', 'qlcv'); ?> *</label>
                                            <input type="number" class="form-control" id="qlcv_new_term_id" name="qlcv_new_term_id" 
                                                   placeholder="<?php _e('Nhập ID taxonomy mới', 'qlcv'); ?>" required>
                                            <small class="form-text text-muted"><?php _e('Nhập ID của term mới để thay thế', 'qlcv'); ?></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Posts List -->
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th width="50">
                                                    <input type="checkbox" id="qlcv_select_all" checked> 
                                                    <?php _e('Chọn tất cả', 'qlcv'); ?>
                                                </th>
                                                <th><?php _e('ID', 'qlcv'); ?></th>
                                                <th><?php _e('Tiêu đề bài viết', 'qlcv'); ?></th>
                                                <th><?php _e('Trạng thái', 'qlcv'); ?></th>
                                                <th><?php _e('Ngày tạo', 'qlcv'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($posts_found as $post): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="qlcv_post_ids[]" value="<?php echo $post->ID; ?>" checked class="qlcv_post_checkbox">
                                                </td>
                                                <td><?php echo $post->ID; ?></td>
                                                <td>
                                                    <strong><?php echo esc_html($post->post_title); ?></strong>
                                                    <br><small class="text-muted"><?php echo esc_html($post->post_name); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $post->post_status === 'publish' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($post->post_status); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($post->post_date)); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('<?php _e('Bạn có chắc chắn muốn cập nhật taxonomy cho các bài viết đã chọn?', 'qlcv'); ?>');">
                                        <i class="fa fa-save"></i> <?php _e('Cập nhật taxonomy', 'qlcv'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('qlcv_select_all');
    const postCheckboxes = document.querySelectorAll('.qlcv_post_checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            postCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }
    
    // Update select all state when individual checkboxes change
    postCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(postCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(postCheckboxes).some(cb => cb.checked);
            
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            }
        });
    });
});
</script>

<?php
get_footer();
?>
