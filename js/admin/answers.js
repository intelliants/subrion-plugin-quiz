Ext.onReady(function () {
    if (Ext.get('js-grid-placeholder')) {
        var urlParam = intelli.urlVal('status');

        intelli.answer =
        {
            columns: [
                'selection',
                {name: 'title', title: _t('title'), width: 1},
                {name: 'question', title: _t('question'), width: 250},
                {
                    name: 'correct_answer',
                    title: _t('correct_answer'),
                    width: 150,
                    align: intelli.gridHelper.constants.ALIGN_CENTER,
                    renderer: intelli.gridHelper.renderer.check
                },
                {name: 'date_added', title: _t('date_added'), width: 170},
                'status',
                'update',
                'delete'
            ],
            storeParams: urlParam ? {status: urlParam} : null,
        };

        intelli.answer = new IntelliGrid(intelli.answer, false);
        intelli.answer.toolbar = Ext.create('Ext.Toolbar', {
            items: [
                {
                    emptyText: _t('text'),
                    name: 'text',
                    listeners: intelli.gridHelper.listener.specialKey,
                    xtype: 'textfield'
                }, {
                    emptyText: _t('question'),
                    name: 'question',
                    listeners: intelli.gridHelper.listener.specialKey,
                    xtype: 'textfield'
                }, {
                    displayField: 'title',
                    editable: false,
                    emptyText: _t('status'),
                    id: 'fltStatus',
                    name: 'status',
                    store: intelli.answer.stores.statuses,
                    typeAhead: true,
                    valueField: 'value',
                    xtype: 'combo'
                }, {
                    handler: function () {
                        intelli.gridHelper.search(intelli.answer);
                    },
                    id: 'fltBtn',
                    text: '<i class="i-search"></i> ' + _t('search')
                }, {
                    handler: function () {
                        intelli.gridHelper.search(intelli.answer, true);
                    },
                    text: '<i class="i-close"></i> ' + _t('reset')
                }
            ]
        });

        if (urlParam) {
            Ext.getCmp('fltStatus').setValue(urlParam);
        }

        intelli.answer.init();
    }
});