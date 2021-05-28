(($) => {
    const getPopup = () => $('.acf-fc-popup');
    const getCurrentPopup = () => getPopup().filter(':visible:last');

    const prepare = (p) => {
        const groups = [];
        const search_container = $('<div class="offbeat-acf-component-search"><ul></ul></div>').hide();
        const list_container = $('<div class="offbeat-acf-current-context offbeat-acf-component-list groups">');
        const list = $('<ul>');

        /**
         * Add the search container to the popup.
         */
        p.append(search_container);

        /**
         * Reads the anchors from the current list and tries to create groups out of it.
         */
        p.find('li a', p).each((_, anchor) => {
            const itemLabel = $(anchor).text();
            let groupLabel = $(anchor).data('category');

            if (!groupLabel) {
                groupLabel = 'General';
            }

            const item = $(anchor).closest('li');

            $(anchor).text(itemLabel);

            if (!groups[groupLabel]) {
                groups[groupLabel] = []
            }

            groups[groupLabel].push(item.clone());
        });

        let grouplabels = Object.keys(groups);

        /**
         * Group creation failed or no groups found.
         */
        if (grouplabels.length <= 1) {
            p.find('>ul').wrap(list_container.removeClass('groups'));
            return;
        }

        /**
         * Sort the groups by name.
         */
        grouplabels.sort((a, b) => {
            const x = a[1].toLowerCase();
            const
                y = b[1].toLowerCase();

            return x < y ? -1 : x > y ? 1 : 0;
        });

        /**
         * Attach the items to the groups.
         */
        grouplabels.forEach((grouplabel) => {
            // get the ul inside the list-item but append it to the list first
            const container = $(`<li><span>${grouplabel}</span><ul style="display:none"></ul></li>`).appendTo(list).find('ul');
            groups[grouplabel].forEach((item) => {
                container.append(item);
            });
        });

        /**
         * Wrap the list and prepend it to the popup.
         */
        list.prependTo(p).wrap(list_container);

        /**
         * Remove the empty UL that remains.
         */
        p.find('>ul').remove();
    };

    const attach = (p) => {

        /**
         * Finds the current context.
         * @returns {*}
         */
        const getContext = () => p.find('.offbeat-acf-current-context');

        /**
         * Find the current active element inside the current active context.
         * If you pass in an element and it's a list-item, then that will be returned.
         * @param element
         * @returns {{length}|jQuery|*|jQuery|HTMLElement}
         */
        const getCurrent = (element) => {
            if (element && $(element).is('li')) {
                return $(element);
            }
            let current = $('li.active li.active', getContext());
            if (!current.length) {
                current = $('li.active', getContext());
            }
            if (!current.length) {
                return;
            }
            return current;
        };

        p
            .on('click', 'li', (e) => {
                // the target can be a child of the list item
                const target = !$(e.target).is('li') ? $(e.target).closest('li') : $(e.target);
                if (target.is($('>ul>li', getContext()))) {
                    target.trigger('offbeat.group.click');
                }
            })
            .on('offbeat.group.click', (e) => {
                const current = getCurrent(e.target);
                if (!current) {
                    return;
                }
                if ($('>a', current).length) {
                    $('>a', current).trigger('click');
                } else if (current.is($('>ul>li', getContext()))) {
                    // toggling
                    if (current.find('>ul').is(':visible')) {
                        current.trigger('offbeat.close');
                    } else {
                        current.trigger('offbeat.open');
                    }
                }
            })
            .on('offbeat.group.open', (e) => {
                const current = getCurrent(e.target);
                if (!current) {
                    return;
                }
                if (current.is($('>ul>li', getContext()))) {
                    current.siblings().trigger('offbeat.group.close');
                    current.trigger('offbeat.item.active');
                    current.find('>ul').show();
                    current.find('>ul>li:first').trigger('offbeat.item.active');
                } else if (current.is($('>ul>li>ul>li', getContext()))) {
                    current.closest('ul').show();
                }
            })
            .on('offbeat.group.close', (e) => {
                const current = getCurrent(e.target);
                if (!current) {
                    return;
                }
                // both target the same ul
                if (current.is($('>ul>li', getContext()))) {
                    current.find('>ul').hide();
                } else if (current.is($('>ul>li>ul>li', getContext()))) {
                    current.closest('ul').hide();
                    current.trigger('offbeat.item.inactive');
                }
            })
            .on('offbeat.item.active', (e) => {
                if ($(e.target).is($('li', getContext()))) {
                    $(e.target).trigger('offbeat.inactive');
                    $(e.target).addClass('active')
                }
            })
            .on('offbeat.item.inactive', (e) => {
                if ($(e.target).is($('li', getContext()))) {
                    $(e.target).siblings('li').addBack().removeClass('active');
                }
            })
            .on('offbeat.item.next', () => {
                const current = getCurrent();
                if (!current) {
                    return;
                }
                let next = current.nextAll(':visible').first();
                if (!next.length) {
                    next = current.siblings(':visible').first();
                }
                if (!next.length) {
                    return;
                }
                next.trigger('offbeat.item.active');
            })
            .on('offbeat.item.prev', () => {
                const current = getCurrent();
                if (!current) {
                    return;
                }
                let prev = current.prevAll(':visible').first();
                if (!prev.length) {
                    prev = current.siblings(':visible').last();
                }
                if (!prev.length) {
                    return;
                }
                prev.trigger('offbeat.item.active');
            })
            .on('offbeat.search', (e, s) => {
                const re = new RegExp(s, 'i');

                const list = p.find('.offbeat-acf-component-list');
                const search = p.find('.offbeat-acf-component-search');

                if (s) {
                    list.removeClass('offbeat-acf-current-context').hide();
                    const container = $('ul', search);
                    container.empty();

                    const stash = [];

                    const selector = list.hasClass('groups') ? '>ul>li>ul>li' : '>ul>li';

                    $(selector, list).each((_, _elLi) => {
                        const elLi = $(_elLi);
                        if (re.test($(elLi).text())) {
                            stash.push([$(elLi).clone(true), $(elLi).text()]);
                        }
                    });

                    stash.sort((a, b) => {
                        const x = a[1].toLowerCase(); const
                            y = b[1].toLowerCase();

                        return x < y ? -1 : x > y ? 1 : 0;
                    }).forEach((o) => {
                        container.append(o[0]);
                    });

                    search.addClass('offbeat-acf-current-context').show();
                    $('>ul>li:first', search).trigger('offbeat.item.active');
                } else {
                    list.addClass('offbeat-acf-current-context').show();
                    search.removeClass('offbeat-acf-current-context').hide();
                }
            });

        /**
         * Upon creation, set the first item active and open the first group.
         */
        $('>ul>li:first', getContext())
            .trigger('offbeat.item.active')
            .trigger('offbeat.group.open');

        /**
         * Create the field.
         * @type {*|jQuery|HTMLElement}
         */
        let fldSearch = $('input[type="text"]', p);

        if (!fldSearch.length) {
            /**
             * Add logic which trigger the offbeat events which animates the circus.
             */
            fldSearch = $('<input type="text" placeholder="Search component" />');
            fldSearch
                .on('keyup', (e) => {
                    if (e.keyCode === 13) {
                        p.trigger('offbeat.group.click');
                    } else if ($.inArray(e.keyCode, [37, 39]) >= 0) {
                        if (e.keyCode === 37) {
                            p.trigger('offbeat.group.close');
                        } else if (e.keyCode === 39) {
                            p.trigger('offbeat.group.open');
                        }
                    } else if ($.inArray(e.keyCode, [38, 40]) === -1) {
                        p.trigger('offbeat.search', [$(e.target).val()]);
                    }
                })
                .on('keydown', (e) => {
                    if (e.keyCode === 38) {
                        $(p).trigger('offbeat.item.prev');
                    } else if (e.keyCode === 40) {
                        $(p).trigger('offbeat.item.next');
                    }
                });

            p.prepend(fldSearch);
        }

        fldSearch.trigger('focus');
    };

    $(document).ready(() => {
        /**
         * Cancel clicks that bubble up from the popup as some event handler closes the div when clicked inside
         * the popup but not on an anchor.
         */
        $('body').on('click', '.acf-fc-popup', (e) => {
            // set the focus onto the input after someone has clicked somewhere inside the popup
            $(e.currentTarget).find(':text').trigger('focus');
            return false;
        });

        $(document).on('click', 'a[data-name="add-component"]', (e) => {
            prepare(getCurrentPopup());
            attach(getCurrentPopup());

            const startPositionTop = parseInt($(e.currentTarget).offset().top);
            const adjustPosition = (p) => {
                if (p.hasClass('top')) {
                    p.css('top', startPositionTop - parseInt(p.outerHeight()));
                }
            };
            adjustPosition(getCurrentPopup());
            getCurrentPopup().on('offbeat.search', () => {
                adjustPosition(getCurrentPopup());
            });
        });
    });

    const setupAcfLayoutField = function () {
        if (typeof acf == 'undefined') return;

        var flexible = acf.getFieldType('offbeat_components');
        var model = flexible.prototype;
    
    
        model.events['click [data-acfe-flexible-control-clone]'] = 'acfeCloneComponent';
        model.acfeCloneComponent = function(e, $el){
    
            // Get Flexible
            var flexible = this;
            
            // Vars
            var $layout = $el.closest('.layout');
            var layout_name = $layout.data('layout');
            
            // Popup min/max
            var $popup = $(flexible.$popup().html());
            var $layouts = flexible.$layouts();
    
            var countLayouts = function(name){
                return $layouts.filter(function(){
                    return $(this).data('layout') === name;
                }).length;
            };
            
             // vars
            var $a = $popup.find('[data-layout="' + layout_name + '"]');
            var min = $a.data('min') || 0;
            var max = $a.data('max') || 0;
            var count = countLayouts(layout_name);
            
            // max
            if(max && count >= max){
                
                $el.addClass('disabled');
                return false;
                
            }else{
                
                $el.removeClass('disabled');
                
            }
            
            // Fix inputs
            flexible.acfeFixInputs($layout);
            
            var $_layout = $layout.clone();
            
            // Clean Layout
            flexible.acfeCleanLayouts($_layout);
            
            var parent = $el.closest('.acf-flexible-content').find('> input[type=hidden]').attr('name');
            
            // Clone
            var $layout_added = flexible.acfeDuplicate({
                layout: $_layout,
                before: $layout,
                parent: parent
            });
        }
    
        // Flexible: Duplicate
        model.acfeDuplicate = function(args){
            
            // Arguments
            args = acf.parseArgs(args, {
                layout: '',
                before: false,
                parent: false,
                search: '',
                replace: '',
            });
            
            // Validate
            if(!this.allowAdd())
                return false;
            
            var uniqid = acf.uniqid();
            
            if(args.parent){
                
                if(!args.search){
                    
                    args.search = args.parent + '[' + args.layout.attr('data-id') + ']';
                    
                }
                
                args.replace = args.parent + '[' + uniqid + ']';
                
            }
            
            if (args.layout) {
                $(args.layout).find('input[name!=""], select[name!=""], textarea[name!=""]').each(function () {
                    if (typeof $(this).attr('name') === 'undefined' || $(this).attr('name').indexOf('[acf_component]') !== -1) return;

                    var current_field_name = $(this).attr('name');
                    var fieldNameKey = current_field_name.substring(current_field_name.lastIndexOf('['));
                    fieldNameKey = fieldNameKey.replace('[', '');
                    fieldNameKey = fieldNameKey.replace(']', '');

                    $(this).attr('name', 'acf[clone][' + fieldNameKey + ']');
                });
            }

            // Add row
            var duplicate_args = {
                target: args.layout,
                search: args.search,
                replace: args.replace,
                append: this.proxy(function($el, $el2){
                    
                    // Add class to duplicated layout
                    $el2.addClass('acfe-layout-duplicated');
                    
                    // Reset UniqID
                    $el2.attr('data-id', uniqid);

                    // append before
                    if(args.before){
                        
                        // Fix clone: Use after() instead of native before()
                        args.before.after($el2);
                        
                    }
                    
                    // append end
                    else{
                        
                        this.$layoutsWrap().append($el2);
                        
                    }
                    
                    // enable 
                    acf.enable($el2, this.cid);
                    
                    // render
                    this.render();
                    
                })
            };

            var acfVersion = parseFloat(acf.get('acf_version'));

            if (acfVersion < 5.9) {

                // Add row
                var $el = acf.duplicate(duplicate_args);

                // Hotfix for ACF Pro 5.9
            } else {

                // Add row
                var $el = model.acfeNewAcfDuplicate(duplicate_args);

            }
            
            // trigger change for validation errors
            this.$input().trigger('change');
            
            // return
            return $el;
            
        }
        
        // Flexible: Fix Inputs
        model.acfeFixInputs = function($layout){
            
            $layout.find('input').each(function(){
                
                $(this).attr('value', this.value);
                
            });
            
            $layout.find('textarea').each(function(){
                
                $(this).html(this.value);
                
            });
            
            $layout.find('input:radio,input:checkbox').each(function() {
                
                if(this.checked)
                    $(this).attr('checked', 'checked');
                
                else
                    $(this).attr('checked', false);
                
            });
            
            $layout.find('option').each(function(){
                
                if(this.selected)
                    $(this).attr('selected', 'selected');
                    
                else
                    $(this).attr('selected', false);
                
            });
            
        }
        
        
    /*
     * Based on acf.duplicate (5.9)
     *
     * doAction('duplicate) has been commented out
     * This fix an issue with the WYSIWYG editor field during copy/paste since ACF 5.9
     */
    model.acfeNewAcfDuplicate = function(args) {

        // allow jQuery
        if (args instanceof jQuery) {
            args = {
                target: args
            };
        }

        // defaults
        args = acf.parseArgs(args, {
            target: false,
            search: '',
            replace: '',
            rename: true,
            before: function($el) {},
            after: function($el, $el2) {},
            append: function($el, $el2) {
                $el.after($el2);
            }
        });

        // compatibility
        args.target = args.target || args.$el;

        // vars
        var $el = args.target;

        // search
        args.search = args.search || $el.attr('data-id');
        args.replace = args.replace || acf.uniqid();

        // before
        // - allow acf to modify DOM
        // - fixes bug where select field option is not selected
        args.before($el);
        acf.doAction('before_duplicate', $el);

        // clone
        var $el2 = $el.clone();

        // rename
        if (args.rename) {
            acf.rename({
                target: $el2,
                search: args.search,
                replace: args.replace,
                replacer: (typeof args.rename === 'function' ? args.rename : null)
            });
        }

        // remove classes
        $el2.removeClass('acf-clone');
        $el2.find('.ui-sortable').removeClass('ui-sortable');

        // after
        // - allow acf to modify DOM
        args.after($el, $el2);
        acf.doAction('after_duplicate', $el, $el2);

        // append
        args.append($el, $el2);

        /**
         * Fires after an element has been duplicated and appended to the DOM.
         *
         * @date    30/10/19
         * @since   5.8.7
         *
         * @param   jQuery $el The original element.
         * @param   jQuery $el2 The duplicated element.
         */
        //acf.doAction('duplicate', $el, $el2 );

        // append
        acf.doAction('append', $el2);

        // return
        return $el2;
    };

        // Flexible: Clean Layout
        model.acfeCleanLayouts = function($layout){
            
            // Clean WP Editor
            $layout.find('.acf-editor-wrap').each(function(){
                
                var $input = $(this);
                
                $input.find('.wp-editor-container div').remove();
                $input.find('.wp-editor-container textarea').css('display', '');
                
            });
            
            // Clean Date
            $layout.find('.acf-date-picker').each(function(){
                
                var $input = $(this);
                
                $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');
                
            });
            
            // Clean Time
            $layout.find('.acf-time-picker').each(function(){
                
                var $input = $(this);
                
                $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');
                
            });
            
            // Clean DateTime
            $layout.find('.acf-date-time-picker').each(function(){
                
                var $input = $(this);
                
                $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');
                
            });
            
            // Clean Color Picker
            $layout.find('.acf-color-picker').each(function(){
                
                var $input = $(this);
                
                var $color_picker = $input.find('> input');
                var $color_picker_proxy = $input.find('.wp-picker-container input.wp-color-picker').clone();
                
                $color_picker.after($color_picker_proxy);
                
                $input.find('.wp-picker-container').remove();
                
            });
            
            // Clean Post Object
            $layout.find('.acf-field-post-object').each(function(){
                
                var $input = $(this);
                
                $input.find('> .acf-input span').remove();
                
                $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();
                
            });
            
            // Clean Page Link
            $layout.find('.acf-field-page-link').each(function(){
                
                var $input = $(this);
                
                $input.find('> .acf-input span').remove();
                
                $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();
                
            });
            
            // Clean Select2
            $layout.find('.acf-field-select').each(function(){
                
                var $input = $(this);
                
                $input.find('> .acf-input span').remove();
                
                $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();
                
            });
            
            // Clean FontAwesome
            $layout.find('.acf-field-font-awesome').each(function(){
                
                var $input = $(this);
                
                $input.find('> .acf-input span').remove();
                
                $input.find('> .acf-input select').removeAttr('tabindex aria-hidden');
                
            });
            
            // Clean Tab
            $layout.find('.acf-tab-wrap').each(function(){
                
                var $wrap = $(this);
                
                var $content = $wrap.closest('.acf-fields');
                
                var tabs = []
                $.each($wrap.find('li a'), function(){
                    
                    tabs.push($(this));
                    
                });
                
                $content.find('> .acf-field-tab').each(function(){
                    
                    $current_tab = $(this);
                    
                    $.each(tabs, function(){
                        
                        var $this = $(this);
                        
                        if($this.attr('data-key') != $current_tab.attr('data-key'))
                            return;
                        
                        $current_tab.find('> .acf-input').append($this);
                        
                    });
                    
                });
                
                $wrap.remove();
                
            });
            
            // Clean Accordion
            $layout.find('.acf-field-accordion').each(function(){
                
                var $input = $(this);
                
                $input.find('> .acf-accordion-title > .acf-accordion-icon').remove();
                
                // Append virtual endpoint after each accordion
                $input.after('<div class="acf-field acf-field-accordion" data-type="accordion"><div class="acf-input"><div class="acf-fields" data-endpoint="1"></div></div></div>');
                
            });
            
        }
    
        function addCloneToLayout(layout) {
            var $controls = layout.find('> .acf-fc-layout-controls');
    
            if(!$controls.has('[data-acfe-flexible-control-clone]').length){
                
                $controls.prepend('<a class="acf-icon small light acf-js-tooltip acfe-flexible-icon dashicons dashicons-admin-page" href="#" title="Clone component" data-acfe-flexible-control-clone="' + layout.attr('data-component') + '"></a>');
                
            }
        }
    
        acf.addAction('new_field/type=offbeat_components', function(offbeat_components_field){
            var $layouts = offbeat_components_field.$layouts();
            
            // Do Actions
            $layouts.each(function(){
                
                addCloneToLayout($(this));
                
            });
    
        });
    
        acf.add_action('append', function( item ) {
            if ($(item).has('.component')) {
                addCloneToLayout($(item));
            }
        });
        
    }

    setupAcfLayoutField();

})(jQuery);