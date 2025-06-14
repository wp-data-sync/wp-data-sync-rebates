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

                    if ($.inArray(parseInt(val), $thisVal)) {
                        $this.show();
                    } else {
                        $this.hide();
                    }


                } else {

                    if (typeof $this.data(key) === 'string' && val === $thisVal) {
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

            sort(val[0], val[1]);
        });

        filterElement.on('change', function () {
            searchElement.val('');
            let val = $(this).val().split('||');

            filter(val[0], val[1]);
        });

        searchElement.on('input', function () {
            filterElement.val('brand||0').trigger('change.select2');
            let val = $(this).val();

            search(val.toLowerCase());
        });

    }

});