Ext.onReady(function () {
    var pageUrl = intelli.config.admin_url + '/quiz/';

    if (Ext.get('js-grid-placeholder')) {
        var urlParam = intelli.urlVal('status');

        intelli.quiz =
        {
            columns: [
                'selection',
                {name: 'title', title: _t('title'), width: 1},
                {name: 'questions_num', title: _t('questions_num'), width: 170},
                {name: 'answers_num', title: _t('answers_num'), width: 170},
                {name: 'date_added', title: _t('date_added'), width: 170},
                {name: 'order', title: _t('order'), width: 70, editor: 'text'},
                'status',
                'update',
                'delete'
            ],
            storeParams: urlParam ? {status: urlParam} : null
        };

        intelli.quiz = new IntelliGrid(intelli.quiz, false);
        intelli.quiz.toolbar = Ext.create('Ext.Toolbar', {
            items: [
                {
                    emptyText: _t('text'),
                    name: 'text',
                    listeners: intelli.gridHelper.listener.specialKey,
                    xtype: 'textfield'
                }, {
                    displayField: 'title',
                    editable: false,
                    emptyText: _t('status'),
                    id: 'fltStatus',
                    name: 'status',
                    store: intelli.quiz.stores.statuses,
                    typeAhead: true,
                    valueField: 'value',
                    xtype: 'combo'
                }, {
                    handler: function () {
                        intelli.gridHelper.search(intelli.quiz);
                    },
                    id: 'fltBtn',
                    text: '<i class="i-search"></i> ' + _t('search')
                }, {
                    handler: function () {
                        intelli.gridHelper.search(intelli.quiz, true);
                    },
                    text: '<i class="i-close"></i> ' + _t('reset')
                }
            ]
        });

        if (urlParam) {
            Ext.getCmp('fltStatus').setValue(urlParam);
        }

        intelli.quiz.init();
    }

    intelli.titleCache = '';
    intelli.fillUrlBox = function () {
        var id = $('input[name="id"]').val();
        var slug = $('#field_title_slug').val();
        var title = ('' == slug ? $('input:first', '#title_fieldzone').val() : slug);
        var cache = title + '%%';

        if ('' !== title && intelli.titleCache != cache) {
            $.get(pageUrl + 'slug.json', {id: id, title: title}, function (response) {
                if ('' !== response.data) {
                    $('#title_url').text(response.data);
                    $('#title_box').fadeIn();
                }
            });
        }

        intelli.titleCache = cache;
    };

    $(function () {
        $('#title_fieldzone input:first, #field_title_slug').blur(intelli.fillUrlBox).blur();
    });
});