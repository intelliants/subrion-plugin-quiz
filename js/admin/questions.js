Ext.onReady(function () {
    if (Ext.get('js-grid-placeholder')) {
        var urlParam = intelli.urlVal('status');

        intelli.question =
        {
            columns: [
                'selection',
                {name: 'title', title: _t('title'), width: 1},
                {name: 'quiz', title: _t('quiz'), width: 250},
                {name: 'answers_num', title: _t('answers_num'), width: 170},
                {name: 'date_added', title: _t('date_added'), width: 170},
                'status',
                'update',
                'delete'
            ],
            storeParams: urlParam ? {status: urlParam} : null
        };

        intelli.question = new IntelliGrid(intelli.question, false);
        intelli.question.toolbar = Ext.create('Ext.Toolbar', {
            items: [
                {
                    emptyText: _t('question'),
                    name: 'text',
                    listeners: intelli.gridHelper.listener.specialKey,
                    xtype: 'textfield'
                }, {
                    emptyText: _t('quiz'),
                    name: 'quiz',
                    listeners: intelli.gridHelper.listener.specialKey,
                    xtype: 'textfield'
                }, {
                    displayField: 'title',
                    editable: false,
                    emptyText: _t('status'),
                    id: 'fltStatus',
                    name: 'status',
                    store: intelli.question.stores.statuses,
                    typeAhead: true,
                    valueField: 'value',
                    xtype: 'combo'
                }, {
                    handler: function () {
                        intelli.gridHelper.search(intelli.question);
                    },
                    id: 'fltBtn',
                    text: '<i class="i-search"></i> ' + _t('search')
                }, {
                    handler: function () {
                        intelli.gridHelper.search(intelli.question, true);
                    },
                    text: '<i class="i-close"></i> ' + _t('reset')
                }
            ]
        });

        if (urlParam) {
            Ext.getCmp('fltStatus').setValue(urlParam);
        }

        intelli.question.init();
    }
});