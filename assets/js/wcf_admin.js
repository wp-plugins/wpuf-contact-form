/**
 * Created by mithu_000 on 8/1/2015.
 */

(function($){

    var wcf_admin = {
        init:function(){
            // add field click
            $('.wcf-form-buttons').on('click', 'button', this.addNewField);
        },
        addNewField: function(e) {
            e.preventDefault();

            var $self = $(this),
                $formEditor = $('ul#wpuf-form-editor'),
                name = $self.data('name'),
                type = $self.data('type'),
                data = {
                    name: name,
                    type: type,
                    order: $formEditor.find('li').length + 1,
                    action: 'wcf_form_add_el'
                };

            // console.log($self, data);

            // check if these are already inserted
            var oneInstance = ['post_title', 'post_content', 'post_excerpt', 'featured_image',
                'user_login', 'first_name', 'last_name', 'nickname', 'user_email', 'user_url',
                'user_bio', 'password', 'user_avatar'];

            if ($.inArray(name, oneInstance) >= 0) {
                if( $formEditor.find('li.' + name).length ) {
                    alert('You already have this field in the form');
                    return false;
                }
            }

            $('.wpuf-loading').removeClass('hide');

            $.post(ajaxurl, data, function(res) {
                $formEditor.append(res);

                // re-call sortable
                Editor.makeSortable();

                // enable tooltip
                Editor.tooltip();

                $('.wpuf-loading').addClass('hide');
                Editor.showHideHelp();
            });
        }
    }

    $(document).ready(function(){
        wcf_admin.init();
    })
}(jQuery))
