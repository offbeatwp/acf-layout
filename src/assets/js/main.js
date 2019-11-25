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
})(jQuery);