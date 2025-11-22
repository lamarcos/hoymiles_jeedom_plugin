
$('#bt_resethoymiles').on('click', function () {
    $('#div_alert').showAlert({message: '{{Opération réalisée avec succès}}', level: 'success'});
});

$(".li_eqLogic").on('click', function (event) {
    return jeedom.eqLogic.cache.getCmd({id: $(this).attr('data-cmd_id')});
});

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}

function printEqLogic(_eqLogic) {
    if (!isset(_eqLogic)) {
        var _eqLogic = {configuration: {}};
    }
    if (!isset(_eqLogic.configuration)) {
        _eqLogic.configuration = {};
    }
    $('.eqLogicAttr').value('');
    $('.eqLogic').show();

    $('.eqLogicAttr[data-l1key=id]').value(_eqLogic.id);
    $('.eqLogicAttr[data-l1key=name]').value(_eqLogic.name);
    $('.eqLogicAttr[data-l1key=object_id]').value(_eqLogic.object_id);
    $('.eqLogicAttr[data-l1key=category]').value([]);

    if (isset(_eqLogic.category)) {
        for (var i in _eqLogic.category) {
            if (_eqLogic.category[i] == 1) {
                $('.eqLogicAttr[data-l1key=category][data-l2key=' + i + ']').prop('checked', true);
            }
        }
    }

    $('.eqLogicAttr[data-l1key=isEnable]').prop('checked', _eqLogic.isEnable == 1);
    $('.eqLogicAttr[data-l1key=isVisible]').prop('checked', _eqLogic.isVisible == 1);

    $('#table_cmd tbody').empty();
    for (var i in _eqLogic.cmd) {
        addCmdToTable(_eqLogic.cmd[i]);
    }

    $('.eqLogicDisplayCard').removeClass('active');
    $('.eqLogicDisplayCard[data-eqLogic_id=' + _eqLogic.id + ']').addClass('active');

    $('.cmdAction[data-action=add]').off('click').on('click', function () {
        addCmdToTable({});
    });

    $('.eqLogicAttr').each(function () {
        if ($(this).attr('data-l1key') != undefined) {
            if ($(this).attr('data-l1key').indexOf('configuration') !== -1) {
                var key = $(this).attr('data-l1key').replace('configuration.', '');
                if (isset(_eqLogic.configuration[key])) {
                    if ($(this).attr('type') === 'checkbox') {
                        $(this).prop('checked', _eqLogic.configuration[key] == 1);
                    } else {
                        $(this).value(_eqLogic.configuration[key]);
                    }
                }
            }
        }
    });
}

$('.pluginAction[data-action=openLocation]').on('click', function () {
    window.open($(this).attr("data-location"), "_blank", null);
});

$('.eqLogicAction[data-action=save]').on('click', function () {
    var eqLogic = $('.eqLogic').getValues('.eqLogicAttr')[0];
    eqLogic.cmd = $('.cmd').getValues('.cmdAttr');
    $.ajax({
        type: 'POST',
        url: 'core/ajax/eqLogic.ajax.php',
        data: {
            action: 'save',
            eqLogic: JSON.stringify(eqLogic)
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            $('#div_alert').showAlert({message: '{{Sauvegarde réussie}}', level: 'success'});
            printEqLogic(data.result);
        }
    });
});
