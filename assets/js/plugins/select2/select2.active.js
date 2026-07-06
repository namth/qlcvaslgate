(function ($) {
    "use strict";
    
    /*Select2*/
    if( $('.select2').length ) {
        $('.select2').select2();
    }
    
    /*Select2 Tags*/
    if( $('.select2-tags').length ) {
        $('.select2-tags').select2({
            tags: true
        });
    }

    /*ASL Autocomplete Input Lazy Load*/
    $(document).ready(function() {
        var ajaxUrl = AJAX.ajax_url;

        // Autocomplete handler
        $('.asl-autocomplete-input').on('input', function() {
            var $input = $(this);
            var $wrapper = $input.closest('.asl-autocomplete-wrapper');
            var $valueInput = $wrapper.find('.asl-autocomplete-value');
            var $suggestions = $wrapper.find('.asl-autocomplete-suggestions');
            var query = $input.val().trim();
            var type = $wrapper.data('type');
            var step = $wrapper.data('step') || 0;

            // Clear hidden ID when input is cleared
            if (query === '') {
                $valueInput.val('');
                $suggestions.empty().hide();
                return;
            }

            // Only search if length >= 3
            if (query.length < 3) {
                $suggestions.empty().hide();
                return;
            }

            // Show a loading indicator
            $suggestions.html('<div style="padding: 8px 12px; color: #888;">Đang tìm kiếm...</div>').show();

            // Perform AJAX request
            $.ajax({
                url: ajaxUrl,
                method: 'GET',
                dataType: 'json',
                data: {
                    action: 'asl_search_select2',
                    type: type,
                    step: step,
                    q: query
                },
                success: function(data) {
                    $suggestions.empty();
                    if (data && data.results && data.results.length > 0) {
                        $.each(data.results, function(index, item) {
                            var $item = $('<div style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee; background: #fff;" class="suggestion-item"></div>')
                                .text(item.text)
                                .data('id', item.id)
                                .data('text', item.text)
                                .hover(
                                    function() {
                                        $suggestions.find('.suggestion-item').removeClass('keyboard-active').css('background', '#fff');
                                        $(this).css('background', '#f5f5f5');
                                    },
                                    function() { $(this).css('background', '#fff'); }
                                );
                            $suggestions.append($item);
                        });
                    } else {
                        $suggestions.html('<div style="padding: 8px 12px; color: #888;">Không tìm thấy kết quả</div>');
                    }
                },
                error: function() {
                    $suggestions.html('<div style="padding: 8px 12px; color: red;">Lỗi tìm kiếm</div>');
                }
            });
        });

        // Handle keyboard navigation
        $('.asl-autocomplete-input').on('keydown', function(e) {
            var $input = $(this);
            var $wrapper = $input.closest('.asl-autocomplete-wrapper');
            var $suggestions = $wrapper.find('.asl-autocomplete-suggestions');
            var $items = $suggestions.find('.suggestion-item');

            if (!$suggestions.is(':visible') || $items.length === 0) {
                return;
            }

            var $active = $suggestions.find('.suggestion-item.keyboard-active');
            var index = $items.index($active);

            if (e.keyCode === 40) { // Down arrow
                e.preventDefault();
                index++;
                if (index >= $items.length) {
                    index = 0;
                }
                $items.removeClass('keyboard-active').css('background', '#fff');
                $items.eq(index).addClass('keyboard-active').css('background', '#f5f5f5');
            } else if (e.keyCode === 38) { // Up arrow
                e.preventDefault();
                index--;
                if (index < 0) {
                    index = $items.length - 1;
                }
                $items.removeClass('keyboard-active').css('background', '#fff');
                $items.eq(index).addClass('keyboard-active').css('background', '#f5f5f5');
            } else if (e.keyCode === 13) { // Enter key
                if ($active.length > 0) {
                    e.preventDefault();
                    $active.trigger('click');
                }
            } else if (e.keyCode === 27) { // Escape key
                $suggestions.empty().hide();
            }
        });

        // Handle suggestion click
        $(document).on('click', '.asl-autocomplete-suggestions .suggestion-item', function() {
            var $item = $(this);
            var $wrapper = $item.closest('.asl-autocomplete-wrapper');
            var $input = $wrapper.find('.asl-autocomplete-input');
            var $valueInput = $wrapper.find('.asl-autocomplete-value');
            var $suggestions = $wrapper.find('.asl-autocomplete-suggestions');

            var id = $item.data('id');
            var text = $item.data('text');
            var isMultiple = $wrapper.data('multiple') === true || $wrapper.attr('data-multiple') === 'true';

            if (isMultiple) {
                var name = $wrapper.data('name') || 'staff[]';
                // Avoid duplicates
                if ($wrapper.find('input[value="' + id + '"]').length === 0) {
                    var tagHtml = '<span class="asl-tag" style="display:inline-block; margin-bottom:5px; margin-right:5px; background:#f0f0f0; border:1px solid #ccc; padding:4px 8px; border-radius:4px;">' + 
                        text + ' <a href="#" class="asl-remove-tag" data-id="' + id + '" style="color:red; margin-left:5px; text-decoration:none; font-weight:bold;">&times;</a>' + 
                        '<input type="hidden" name="' + name + '" value="' + id + '"></span>';
                    $wrapper.find('.asl-autocomplete-tags').append(tagHtml);
                }
                $input.val('');
            } else {
                $input.val(text);
                $valueInput.val(id);
            }
            $suggestions.empty().hide();
        });

        // Handle tag removal
        $(document).on('click', '.asl-remove-tag', function(e) {
            e.preventDefault();
            $(this).closest('.asl-tag').remove();
        });

        // Close suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.asl-autocomplete-wrapper').length) {
                $('.asl-autocomplete-suggestions').empty().hide();
            }
        });
    });
    
})(jQuery);