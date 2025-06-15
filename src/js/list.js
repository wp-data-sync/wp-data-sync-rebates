jQuery(document).ready(function ($) {

    const list = $('.rebates-table.list tbody');

    if (list.length) {

        const listItems = list.children('tr').get();
        const sortElement = $('.rebates-table.list .rebate-filter-wrap select.rebate-sort');
        const filterElement = $('.rebates-table.list .rebate-filter-wrap select.rebate-filter');
        const searchElement = $('.rebates-table.list .rebate-filter-wrap input.rebate-search');
        const sort = (key, order) => {

            listItems.sort(function (a, b) {
                let textA = $(a).data(key);
                let textB = $(b).data(key);
                if ('asc' === order) {
                    return (textA < textB) ? -1 : (textA > textB) ? 1 : 0; // ASC
                }
                return (textB < textA) ? -1 : (textB > textA) ? 1 : 0; // DESC
            });

            $.each(listItems, function (index, item) {
                list.append(item);
            });

        }
        const filter = (key, val) => {

            $.each(listItems, function () {

                let $this = $(this);
                let $thisVal = $this.data(key);

                if ('0' === val) {
                    $this.show();
                } else if ('cat_ids' === key) {

                    $.each($thisVal, function(i, v) {

                        if ( v === parseInt(val) ) {
                            $this.show();
                            return;
                        }

                        $this.hide();
                    });

                } else {

                    if (val === $thisVal) {
                        $this.show();
                    } else {
                        $this.hide();
                    }

                }

            });

        }
        const search = (val) => {

            $.each(listItems, function () {

                let $this = $(this);
                let $thisVal = $this.data('search');

                if ($thisVal.indexOf(val) !== -1) {
                    $this.show();
                } else {
                    $this.hide();
                }

            });
            val.toLowerCase();

        }

        sortElement.selectWoo({
            width: '100%'
        });

        filterElement.selectWoo({
            width: '100%'
        });

        /**
         * Sort Order
         */
        sortElement.on('change', function () {
            let val = $(this).val().split('||');

            list.height(list.height()).fadeOut().promise().done(function() {
                sort(val[0], val[1]);
                list.css('height', 'auto').fadeIn();
            });
        });

        filterElement.on('change', function () {
            let val = $(this).val().split('||');

            list.height(list.height()).fadeOut().promise().done(function() {
                searchElement.val('');
                filter(val[0], val[1]);
                list.css('height', 'auto').fadeIn();
            });
        });

        searchElement.on('input', function () {
            let val = $(this).val();

            list.height(list.height()).fadeOut().promise().done(function() {
                filterElement.val('brand||0').trigger('change.select2');
                search(val.toLowerCase());
                list.css('height', 'auto').fadeIn();
            });
        });

    }

});