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
            $thongbao = '<div class="alert alert-warning">' . 'Không tìm thấy bài viết nào với điều kiện tìm kiếm' . '</div>';
        }
        wp_reset_postdata();
    } else {
        $thongbao = '<div class="alert alert-danger">' . 'Vui lòng điền đầy đủ thông tin tìm kiếm' . '</div>';
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
    $new_term_input = sanitize_text_field($_POST['qlcv_new_term_id']);
    $old_term_id = intval($_POST['qlcv_old_term_id']);
    
    if (!empty($post_ids) && !empty($taxonomy) && !empty($new_term_input)) {
        $updated_count = 0;
        $errors = array();
        
        // Parse new term IDs - support comma-separated values
        $new_term_input = trim($new_term_input);
        $new_term_ids = array();
        
        if (strpos($new_term_input, ',') !== false) {
            // Multiple IDs separated by comma
            $term_parts = explode(',', $new_term_input);
            foreach ($term_parts as $part) {
                $term_id = intval(trim($part));
                if ($term_id > 0) {
                    $new_term_ids[] = $term_id;
                }
            }
        } else {
            // Single ID
            $term_id = intval($new_term_input);
            if ($term_id > 0) {
                $new_term_ids[] = $term_id;
            }
        }
        
        // Debug: Add info about parsed terms
        $debug_info = 'Parsed term IDs: ' . implode(', ', $new_term_ids);
        
        foreach ($post_ids as $post_id) {
            $post_id = intval($post_id);
            
            // First, remove all existing terms for this taxonomy
            wp_delete_object_term_relationships($post_id, $taxonomy);
            
            // Then add new terms
            $result = wp_set_object_terms($post_id, $new_term_ids, $taxonomy, false);
            
            if (!is_wp_error($result) && is_array($result)) {
                $updated_count++;
            } else {
                $error_msg = is_wp_error($result) ? $result->get_error_message() : 'Unknown error';
                $errors[] = sprintf('Lỗi cập nhật bài viết ID %d: %s', $post_id, $error_msg);
            }
        }
        
        if ($updated_count > 0) {
            $thongbao = '<div class="alert alert-success">' . 
                sprintf('Đã cập nhật thành công %d bài viết với %d taxonomy terms (%s)', $updated_count, count($new_term_ids), $debug_info) . 
                '</div>';
        }
        
        if (!empty($errors)) {
            $thongbao .= '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
        }
        
        if ($updated_count === 0 && empty($errors)) {
            $thongbao = '<div class="alert alert-danger">' . 'Không có bài viết nào được cập nhật. ' . $debug_info . '</div>';
        }
    } else {
        $thongbao = '<div class="alert alert-danger">' . 'Vui lòng điền đầy đủ thông tin cập nhật' . '</div>';
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
                <h3 class="title">Cập nhật taxonomy cho bài viết</h3>
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
                            <h5>Tìm kiếm bài viết</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="#">
                                <?php wp_nonce_field('qlcv_search_nonce', 'qlcv_search_nonce_field'); ?>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="qlcv_post_type">Post Type *</label>
                                            <input type="text" class="form-control" id="qlcv_post_type" name="qlcv_post_type" 
                                                   placeholder="Ví dụ: product, event, news" 
                                                   value="<?php echo isset($_POST['qlcv_post_type']) ? esc_attr($_POST['qlcv_post_type']) : ''; ?>" required>
                                            <small class="form-text text-muted">Nhập tên post type cần tìm kiếm</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="qlcv_taxonomy">Taxonomy *</label>
                                            <input type="text" class="form-control" id="qlcv_taxonomy" name="qlcv_taxonomy" 
                                                   placeholder="Ví dụ: product_category, event_type" 
                                                   value="<?php echo isset($_POST['qlcv_taxonomy']) ? esc_attr($_POST['qlcv_taxonomy']) : ''; ?>" required>
                                            <small class="form-text text-muted">Nhập tên taxonomy hiện tại</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="qlcv_current_term_id">ID Taxonomy hiện tại *</label>
                                            <input type="number" class="form-control" id="qlcv_current_term_id" name="qlcv_current_term_id" 
                                                   placeholder="Ví dụ: 5, 10, 25" 
                                                   value="<?php echo isset($_POST['qlcv_current_term_id']) ? esc_attr($_POST['qlcv_current_term_id']) : ''; ?>" required>
                                            <small class="form-text text-muted">Nhập ID của term hiện tại</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-search"></i> Tìm kiếm bài viết
                                </button>
                            </form>
                        </div>
                    </div>

                    <?php if ($search_performed && !empty($posts_found)): ?>
                    <!-- Results and Update Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Kết quả tìm kiếm và cập nhật taxonomy</h5>
                            <p class="mb-0"><?php printf('Tìm thấy %d bài viết', count($posts_found)); ?></p>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <?php wp_nonce_field('qlcv_update_nonce', 'qlcv_update_nonce_field'); ?>
                                
                                <!-- Hidden fields to preserve search data -->
                                <input type="hidden" name="qlcv_taxonomy" value="<?php echo esc_attr($_POST['qlcv_taxonomy']); ?>">
                                <input type="hidden" name="qlcv_old_term_id" value="<?php echo esc_attr($_POST['qlcv_current_term_id']); ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="qlcv_new_term_id">ID Taxonomy mới *</label>
                                            <input type="text" class="form-control" id="qlcv_new_term_id" name="qlcv_new_term_id" 
                                                   placeholder="Ví dụ: 10 hoặc 10,15,20" required>
                                            <small class="form-text text-muted">
                                                Nhập ID taxonomy mới. Có thể nhập nhiều ID cách nhau bởi dấu phẩy (,) để thay thế toàn bộ taxonomy của bài viết<br>
                                                <strong>Lưu ý:</strong> Không có khoảng trắng thừa. Ví dụ đúng: <code>10,15,20</code> - Ví dụ sai: <code>10, 15, 20</code>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Preview</label>
                                            <div id="term_preview" class="form-control-plaintext text-muted">
                                                Nhập ID để xem preview
                                            </div>
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
                                                    Chọn tất cả
                                                </th>
                                                <th>ID</th>
                                                <th>Tiêu đề bài viết</th>
                                                <th>Taxonomy hiện tại</th>
                                                <th>Trạng thái</th>
                                                <th>Ngày tạo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($posts_found as $post): ?>
                                            <?php 
                                            // Get all taxonomy terms for this post
                                            $current_terms = wp_get_object_terms($post->ID, $_POST['qlcv_taxonomy'], array('fields' => 'all'));
                                            $term_info = array();
                                            if (!is_wp_error($current_terms) && !empty($current_terms)) {
                                                foreach ($current_terms as $term) {
                                                    $term_info[] = $term->name . ' (ID: ' . $term->term_id . ')';
                                                }
                                            }
                                            ?>
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
                                                    <?php if (!empty($term_info)): ?>
                                                        <div class="taxonomy-terms">
                                                            <?php foreach ($term_info as $term_display): ?>
                                                                <span class="badge badge-info mr-1 mb-1"><?php echo esc_html($term_display); ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <small class="text-muted">Không có taxonomy</small>
                                                    <?php endif; ?>
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
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Bạn có chắc chắn muốn cập nhật taxonomy cho các bài viết đã chọn?');">
                                        <i class="fa fa-save"></i> Cập nhật taxonomy
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
    
    // Term ID preview functionality
    const termInput = document.getElementById('qlcv_new_term_id');
    const termPreview = document.getElementById('term_preview');
    
    if (termInput && termPreview) {
        termInput.addEventListener('input', function() {
            const value = this.value.trim();
            if (!value) {
                termPreview.innerHTML = 'Nhập ID để xem preview';
                termPreview.className = 'form-control-plaintext text-muted';
                return;
            }
            
            // Parse and validate
            const termIds = [];
            if (value.includes(',')) {
                const parts = value.split(',');
                parts.forEach(function(part) {
                    const id = parseInt(part.trim());
                    if (!isNaN(id) && id > 0) {
                        termIds.push(id);
                    }
                });
            } else {
                const id = parseInt(value);
                if (!isNaN(id) && id > 0) {
                    termIds.push(id);
                }
            }
            
            if (termIds.length > 0) {
                termPreview.innerHTML = `Sẽ áp dụng ${termIds.length} term(s): ${termIds.join(', ')}`;
                termPreview.className = 'form-control-plaintext text-success';
            } else {
                termPreview.innerHTML = 'Định dạng không hợp lệ';
                termPreview.className = 'form-control-plaintext text-danger';
            }
        });
    }
});
</script>

<?php
get_footer();
?>
