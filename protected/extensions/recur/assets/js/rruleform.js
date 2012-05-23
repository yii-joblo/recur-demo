/**
 * Created with JetBrains PhpStorm.
 * User: joe
 * Date: 29.04.12
 * Time: 19:34
 * To change this template use File | Settings | File Templates.
 */

function rruleAddToContainer(formOptions, actionIcons, ruleStr, summaryText, ruleExistsText) {
    var container = $('#' + formOptions.rulesContainerId);
    var ruleContainer = $('<div>').attr('class', 'rrule-container-item');
    var ruleId = 'rr_' + MD5(ruleStr);
    var existingRule = container.find('#' + ruleId);

    if (existingRule.length) //this rule exists
    {
        alert(ruleExistsText);
        return false;
    }

    hiddenInput = $('<input>').attr({
        type:'hidden',
        name:formOptions.id + 'RRULES[]',
        value:ruleStr,
        id:ruleId
    });

    var ruleHtml = $('<span>').html(summaryText).prepend(actionIcons.remove).prepend(actionIcons.calpreview).prepend(actionIcons.listpreview);

    ruleContainer.appendTo(container);
    hiddenInput.appendTo(ruleContainer);
    ruleHtml.appendTo(ruleContainer);

    return true;
}


function toggleTimeInputs(elem, tstartId, tendId)
{
    if ($(elem).val() == '1') {
        $('#' + tstartId).val('');
        $('#' + tstartId).hide();
        $('#' + tendId).val('');
        $('#' + tendId).hide();
        $('div.rruleform fieldset.rrule').attr('class', 'exrule');
    }
    else {
        $('#' + tstartId).show();
        $('#' + tendId).show();
        $('div.rruleform fieldset.exrule').attr('class', 'rrule');
    }
}


function onFreqChange(selectedIdx, widgetId)
{
    //daily
    if (selectedIdx == 0) //disable BYDAY
    {
        var elem = $('#' + widgetId + '_BYDAY');
        elem.multiselect('uncheckAll');
        elem.multiselect('disable');
    }
    else //enable BYDAY
        $('#' + widgetId + '_BYDAY').multiselect('enable');


    //monthly of yearly
    if (selectedIdx > 1) //enable BYMONTHDAY,prefixBYDAY
    {
        $('#' + widgetId + '_BYMONTHDAY').multiselect('enable');
        $('#' + widgetId + '_prefixBYDAY').multiselect('enable');
    }
    else //daily or weekly: disable BYMONTHDAY,prefixBYDAY
    {
        var elem = $('#' + widgetId + '_BYMONTHDAY');
        elem.multiselect('uncheckAll');
        elem.multiselect('disable');

        elem = $('#' + widgetId + '_prefixBYDAY');
        elem.multiselect('uncheckAll');
        elem.multiselect('disable');
    }
}


function onPrefixByDayClose(widgetId)
{
    //disable BYMONTHDAY if at least one of prefixBYDAY is checked
    if($('#' + widgetId + '_prefixBYDAY').multiselect('getChecked').length > 0)
    {
        var elem = $('#' + widgetId + '_BYMONTHDAY');
        elem.multiselect('uncheckAll');
        elem.multiselect('disable');
    }
    else
    {
        $('#' + widgetId + '_BYMONTHDAY').multiselect('enable');
    }
}


function onPrefixByMonthDayClose(widgetId)
{
    //disable _prefixBYDAY if at least one of BYMONTHDAY is checked
    if($('#' + widgetId + '_BYMONTHDAY').multiselect('getChecked').length > 0)
    {
        var elem = $('#' + widgetId + '_prefixBYDAY');
        elem.multiselect('uncheckAll');
        elem.multiselect('disable');
    }
    else
    {
        $('#' + widgetId + '_prefixBYDAY').multiselect('enable');
    }
}
