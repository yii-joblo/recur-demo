<?php
/**
 * RRuleForm.php
 *
 * A widget that provides a form and tools for building recurring rules
 * The model is an instance of RRule
 *
 * PHP version 5.3+
 *
 * @author Joe Blocher <yii@myticket.at>
 * @copyright 2012 myticket it-solutions gmbh
 * @license New BSD License
 * @category Date and Time
 * @package recur
 * @version 0.9
 */

Yii::import('zii.widgets.jui.CJuiInputWidget');
Yii::import('ext.recur.models.*');
Yii::import('ext.recur.components.FullCalendar');

class RRuleForm extends CJuiInputWidget
{

    /**
     * @var string the locale ID (eg 'fr', 'de') for the language to be used by the date picker.
     * Uses Yii::app()->language if not set
     *
     * Note: The static translation function t uses Yii::app()->language, not this setting
     */
    public $language;

    /**
     * Shows the from/to time input if true
     * Set to false only days needed
     *
     * @var bool
     */
    public $timeEnabled = true;

    /**
     * The RRules model for the rulescontainer
     * @var the RRules model
     */
    public $rrules;

    /**
     * The html options for the rulescontainer
     * @var array
     */
    public $rulesContainerHtmlOptions = array();

    /**
     * The view for the recurring form
     * You can create and register your own customized formview here
     *
     * @var string
     */
    public $formView = 'rruleform';

    /**
     * The view for the recurring form
     * You can create and register your own customized formview here
     *
     * @var string
     */
    public $calendarView = 'fullcalendar';

    /**
     * The view that displays the summary text of a rule
     * You can create and register your own customized view here
     * Set this property to the view 'rrule_raw' to display the generated rule string for debugging purpose
     *
     * @var string
     */
    public $ruleSummaryView = 'rrule_summary'; //'rrule_raw';

    /**
     * The id of the widget with 'rrf' as default
     * @var string
     */
    public $id = 'rrf';

    /**
     * The confirm text when deleting a rule from the rulescontainer
     * @var string
     */
    public $removeConfirmText = 'Delete this rule?';

    /**
     * Change this to a already installed EchMultiSelect extension in your project
     * @var string
     */
    public $extEchMultiSelect = 'ext.recur.extensions.EchMultiSelect.EchMultiSelect';

    /**
     * @var string the assets folder
     */
    private $_assets;

    /**
     * Get the assets dir
     * @return string
     */
    public function getAssets()
    {
        if (!isset($this->_assets))
            $this->_assets = Yii::app()->assetManager->publish(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../assets');

        return $this->_assets;
    }

    /**
     * Registers the clientscripts
     */
    public function registerClientScript()
    {
        $cs = Yii::app()->getClientScript();
        $assets = $this->getAssets();

        //code from https://github.com/skyporter/rrule_parser
        $cs->registerScriptFile($assets . '/js/rruleform.js');
        $cs->registerScriptFile($assets . '/js/webtoolkit.md5.js');
        $cs->registerCssFile($assets . '/css/rruleform.css');

        if((!empty($this->theme) || $this->theme != 'base'))
        {
            $this->themeUrl = $assets .'/themes';
        }


        FullCalendar::publishScripts();
    }

    /**
     * Get the language for the jquery ui elements; 'en' if $this->language and  Yii::app()->language is not set
     * Ensure a language string length of 2 for usage with the jquery language support
     *
     * @return string
     */
    public function getLanguage()
    {
        if(!isset($this->language))
            $this->language = isset(Yii::app()->language) ? Yii::app()->language : 'en';

        if(strlen($this->language)>2)
           $this->language = substr($this->language,0,2);

        return $this->language;
    }

    /**
     * Initialize the widget
     */
    public function init()
    {
        parent::init();

        if (!isset($this->model))
            $this->model = new RRule();

        $this->registerClientScript();
    }

    /**
     * Translate a message
     * The files have to be placed in 'ext.recur.messages'
     * 'default.php' is the default category file
     *
     * @static
     * @param $message
     * @param int $plural
     * @param string $category
     * @param string $source
     * @param null $language
     * @return string
     */
    public static function t($message, $plural = 1,
                             $category = 'default', $source = 'rrulemessages')
    {
        $params = array($plural); //see plural messages
        return Yii::t($category, $message, $params, $source, Yii::app()->language);
    }


    /**
     * Get the id for a form element
     *
     * @param $name
     * @return string
     */
    protected function getRRuleElemId($name)
    {
        return $this->id . "_$name";
    }

    /**
     * Get the name for a form element
     *
     * @param $name
     * @return string
     */
    protected function getRRuleElemName($name)
    {
        return $this->id . "[RULE][$name]";
    }

    /**
     * Get the css class for a form element
     *
     * @param $name
     * @return string
     */
    protected function getRRuleCssClass($name)
    {
        return $this->id . "_RRULE_$name";
    }


    /**
     * Create html output for a rule like generated in rruleform.js
     * The configured view $this->ruleSummaryView is used to display a userfriendly rule summary
     *
     * @param $rrule
     * @return string
     */
    protected function ruleAsContainerItem($rrule)
    {
        $ruleStr = $rrule->getRuleStr();
        $ruleId = 'rr_' . md5($ruleStr);

        $hiddenInput = CHtml::hiddenField($this->id . 'RRULES[]', $ruleStr, array('id' => $ruleId));

        $removeLink = $this->getRemoveActionIcon($this->removeConfirmText);
        $listPreviewLink = $this->getAjaxActionIcon('listpreviewrule', array('rule' => $ruleStr));
        $calPreviewLink = $this->getAjaxActionIcon('calpreviewrule', array('rule' => $ruleStr));
        $summary = $this->render($this->ruleSummaryView, array('model' => $rrule),true);

        //need to match with the rruleform.js generated structure and the removelink onclick: parent().parent()...
        $ruleOutput = CHtml::tag('span', array(), $hiddenInput . $listPreviewLink . $calPreviewLink . $removeLink . $summary);

        return CHtml::tag('div', array('class' => 'rrule-container-item'), $ruleOutput);
    }


    /**
     * Get the html output of the rulescontainer
     *
     * @return string
     */
    public function getRulesContainer()
    {
        $htmlOptions = $this->rulesContainerHtmlOptions;
        $htmlOptions['id'] = $this->getRulesContainerId();

        $result = CHtml::openTag('div', $htmlOptions);

        //add the same html as generated by the js-function rruleAddToContainer in rrulform.js
        if (!empty($this->rrules) && !$this->rrules->hasErrors())
        {
            $rrules = $this->rrules->getModels();

            if (!empty($rrules))
                foreach ($rrules as $ruleModel)
                {
                    $result .= $this->ruleAsContainerItem($ruleModel);
                }
        }

        $result .= CHtml::closeTag('div');


        return $result;
    }

    /**
     * Render the action button to preview all datetimes as a list
     *
     * @param string $text
     * @param array $ajaxOptions
     * @param array $htmlOptions
     */
    public function renderRulesListPreviewButton($text = 'Datetimes list preview', $ajaxOptions = array(), $htmlOptions = array())
    {
        $this->ajaxActionButton(self::t($text), 'listpreviewrules', $ajaxOptions, $htmlOptions);
    }

    /**
     * Render the action button to preview all datetimes as a list
     *
     * @param string $text
     * @param array $ajaxOptions
     * @param array $htmlOptions
     */
    public function renderRulesCalPreviewButton($text = 'Datetimes calendar preview', $ajaxOptions = array(), $htmlOptions = array())
    {
       $this->ajaxActionButton(self::t($text), 'calpreviewrules', $ajaxOptions, $htmlOptions);
    }

    /**
     * Render the message container div (previews, errormessages)
     *
     * @param string $text
     * @param array $htmlOptions
     */
    public function renderMessageContainer($text = '', $htmlOptions = array())
    {
        $htmlOptions['id'] = $this->messageContainerId;
        echo CHtml::tag('div', $htmlOptions, $text);
    }


    /**
     * Render the except checkbox
     *
     * @param array $htmlOptions
     * @return string
     */
    public function renderExRule($data=array('0'=>'Repeat','1'=>'Except'),$htmlOptions = array())
    {
        $htmlOptions['class'] = $this->getRRuleCssClass(RRule::rrEXRULE);
        $htmlOptions['name'] = $this->getRRuleElemName(RRule::rrEXRULE);
        $htmlOptions['id'] = $this->getRRuleElemId(RRule::rrEXRULE);

        $tStartId = $this->getRRuleElemId(RRule::rrTSTART);
        $tEndId = $this->getRRuleElemId(RRule::rrTEND);

        $htmlOptions['onclick'] = "toggleTimeInputs(this,'$tStartId','$tEndId');";

        return CHtml::activeRadioButtonList($this->model, RRule::rrEXRULE, $data, $htmlOptions);
        //return CHtml::activeCheckBox($this->model, RRule::rrEXRULE, $htmlOptions);
    }

    /**
     * Get the listdata for FREQ
     *
     * @return array
     */
    public function getListDataFreq()
    {
        $data = array(
            RRule::rrDAILY => self::t('day'),
            RRule::rrWEEKLY => self::t('week'),
            RRule::rrMONTHLY => self::t('month'),
            RRule::rrYEARLY => self::t('year'),
        );
        return $data;
    }

    /**
     * Render FREQ as dropdownlist
     *
     * @param array $htmlOptions
     * @return string
     */
    public function renderRRuleFreq($htmlOptions = array())
    {
        $data = $this->getListDataFreq();

        $htmlOptions['class'] = $this->getRRuleCssClass(RRule::rrFREQ);
        $htmlOptions['name'] = $this->getRRuleElemName(RRule::rrFREQ);
        $htmlOptions['id'] = $this->getRRuleElemId(RRule::rrFREQ);
        $htmlOptions['onChange'] = "onFreqChange(this.selectedIndex,'{$this->id}')";

        return CHtml::activeDropDownList($this->model, RRule::rrFREQ, $data, $htmlOptions);
    }

    /**
     * Internal rendering of a EchMultiSelect
     *
     * @param $attribute
     * @param $data
     * @param $options
     */
    protected function _renderEchMultiSelect($attribute,$data,$options,$htmlOptions)
    {
        $htmlOptions = array_merge($htmlOptions,array(
            'name' => $this->getRRuleElemName($attribute),
            'class' => $this->getRRuleCssClass($attribute),
            'id' => $this->getRRuleElemId($attribute),
        ));

        $this->widget($this->extEchMultiSelect, array(
            'model' => $this->model,
            'dropDownAttribute' => $attribute,
            'data' => $data,
            'theme' => $this->theme,
            'themeUrl' => $this->themeUrl,
            'options' => $options,
            'dropDownHtmlOptions' => $htmlOptions,
        ));
    }

    /**
     * Get the listdata for BYDAY
     *
     * @return array
     */
    public static function getListDataByDay()
    {
        return array(
            RRule::rrMO => self::t('Mo'),
            RRule::rrTU => self::t('Tu'),
            RRule::rrWE => self::t('We'),
            RRule::rrTH => self::t('Th'),
            RRule::rrFR => self::t('Fr'),
            RRule::rrSA => self::t('Sa'),
            RRule::rrSU => self::t('Su'),
        );
    }

    /**
     * Render BYDAY as EchMultiSelect (extension)
     *
     * @param array $htmlOptions
     */
    public function renderRRuleByDay($noneSelText,$options=array(),$htmlOptions = array())
    {
        $data = self::getListDataByDay();

        $options = array_merge(array(
                        'noneSelectedText' => $noneSelText,
                        'selectedText' => Yii::t('application', '#' . 'selected'),
                        'selectedList' => 6,
                        'autoOpen' => false,
                        'multiple' => true,
                        'minWidth' => 200,
                      ),$options);

        $this->_renderEchMultiSelect(RRule::rrBYDAY,$data,$options,$htmlOptions);
    }


    /**
     * Get the listdata for BYDAY
     *
     * @return array
     */
    public static function getListDataPrefixByDay()
    {
        return array(
            '1' => self::t('Every first of the month'),
            '2' => self::t('Every second of the month'),
            '3' => self::t('Every third of the month'),
            '4' => self::t('Every forth of the month'),
            '5' => self::t('Every fifth of the month'),
            '-1' => self::t('Every last of the month'),
            '-2' => self::t('Every second last of the month'),
            '-3' => self::t('Every third last of the month'),
            '-4' => self::t('Every forth last of the month'),
            '-5' => self::t('Every fifth last of the month'),
        );
    }


    /**
     * Render BYDAY as EchMultiSelect (extension)
     *
     * @param array $htmlOptions
     */
    public function renderRRulePrefixByDay($noneSelText,$options=array(),$htmlOptions = array())
    {
        $data = self::getListDataPrefixByDay();

        $options = array_merge(array(
            'noneSelectedText' => $noneSelText,
            'selectedText' => Yii::t('application', '#' . 'selected'),
            'selectedList' => 6,
            'autoOpen' => false,
            'multiple' => true,
            'minWidth' => 280,
        ),$options);

        $this->_renderEchMultiSelect(RRule::rrPrefixBYDAY,$data,$options,$htmlOptions);
    }

    /**
     * Get the listdata for BYMONTH
     *
     * @return array
     */
    public static function getListDataByMonth()
    {
        $data = array(
            '1' => self::t('Jan'),
            '2' => self::t('Feb'),
            '3' => self::t('Mar'),
            '4' => self::t('Apr'),
            '5' => self::t('May'),
            '6' => self::t('Jun'),
            '7' => self::t('Jul'),
            '8' => self::t('Aug'),
            '9' => self::t('Sep'),
            '10' => self::t('Oct'),
            '11' => self::t('Nov'),
            '12' => self::t('Dec'),
        );
        return $data;
    }


    /**
     * Render BYMONTH as EchMultiSelect (extension)
     *
     * @param array $htmlOptions
     */
    public function renderRRuleByMonth($options=array(),$htmlOptions = array())
    {
        $data = self::getListDataByMonth();

        $options = array_merge(array(
            'noneSelectedText' => self::t('All'),
            'selectedText' => Yii::t('application', '#' . 'selected'),
            'selectedList' => 6,
            'autoOpen' => false,
            'multiple' => true,
            'minWidth' => 180
        ),$options);

        $this->_renderEchMultiSelect(RRule::rrBYMONTH,$data,$options,$htmlOptions);
    }

    /**
     * Get the listdata for BYMONTHDAY
     *
     * @return array
     */
    public static function getListDataByMonthDay()
    {
        $data = array();

        for ($i = 1; $i <= 31; $i++)
            $data[$i] = $i;
        return $data;
    }


    /**
     * Render BYMONTHDAY as EchMultiSelect (extension)
     *
     * @param array $htmlOptions
     */
    public function renderRRuleByMonthDay($options=array(),$htmlOptions = array())
    {
        $data = self::getListDataByMonthDay();

        $options = array_merge(array(
            'noneSelectedText' => self::t('-'),
            'selectedText' => Yii::t('application', '#' . 'selected'),
            'selectedList' => 6,
            'autoOpen' => false,
            'multiple' => true,
            'minWidth' => 180,
        ),$options);

        $this->_renderEchMultiSelect(RRule::rrBYMONTHDAY,$data,$options,$htmlOptions);
    }


    /**
     * Get the listdata for COUNT
     *
     * @return array
     */
    public static function getListDataCount($max)
    {
        $data = array();

        for ($i = 0; $i <= $max; $i++)
            $data[$i] = ($i == 0) ? '-' : $i;
        return $data;
    }

    /**
     *
     * Render COUNT as a dropdownlist
     *
     * @param int $max
     * @param array $htmlOptions
     * @return string
     */
    public function renderRRuleCount($max = 365, $htmlOptions = array())
    {
        $data = self::getListDataCount($max);

        $htmlOptions['class'] = $this->getRRuleCssClass(RRule::rrCOUNT);
        $htmlOptions['name'] = $this->getRRuleElemName(RRule::rrCOUNT);
        $htmlOptions['id'] = $this->getRRuleElemId(RRule::rrCOUNT);

        $untilId = $this->getRRuleElemId(RRule::rrUNTIL);

        $htmlOptions['onChange'] = "$('#$untilId').attr('disabled', this.selectedIndex != 0).val('');";

        return CHtml::activeDropDownList($this->model, RRule::rrCOUNT, $data, $htmlOptions);
    }


    /**
     * Get the listdata for INTERVAL
     *
     * @return array
     */
    public static function getListDataInterval($max=366)
    {
        $data = array();

        $emptyText = '-';

        for ($i = 1; $i <= $max; $i++)
            $data[$i] = $i == 1 ? $emptyText : $i .'.';
        return $data;
    }


    /**
     * Render INTERVAL as a dropdownlist
     *
     * @param int $max
     * @param array $htmlOptions
     * @return string
     */
    public function renderRRuleInterval($max = 366, $htmlOptions = array())
    {
        $data = self::getListDataInterval($max);

        $htmlOptions['class'] = $this->getRRuleCssClass(RRule::rrINTERVAL);
        $htmlOptions['name'] = $this->getRRuleElemName(RRule::rrINTERVAL);
        $htmlOptions['id'] = $this->getRRuleElemId(RRule::rrINTERVAL);

        return CHtml::activeDropDownList($this->model, RRule::rrINTERVAL, $data, $htmlOptions);
    }


    /**
     * Render the start date DSTART as CJuiDatePicker
     *
     * @param array $options
     * @param array $htmlOptions
     */
    public function renderDStart($options = array(), $htmlOptions = array())
    {
        $htmlOptions['class'] = $this->getRRuleCssClass(RRule::rrDSTART);
        $htmlOptions['name'] = $this->getRRuleElemName(RRule::rrDSTART);
        $htmlOptions['id'] = $this->getRRuleElemId(RRule::rrDSTART);

        $this->widget('zii.widgets.jui.CJuiDatePicker', array(
            'model' => $this->model,
            'attribute' => RRule::rrDSTART,
            'language' => $this->getLanguage(),
            'theme' => $this->theme,
            'themeUrl' => $this->themeUrl,
            'options' => $options,
            'htmlOptions' => $htmlOptions,
        ));
    }


    /**
     * Render the start time TSTART as JTimePicker
     *
     * @param array $options
     * @param array $htmlOptions
     */
    public function renderTStart($options = array(), $htmlOptions = array('size' => 5, 'maxlength' => 5))
    {
        $htmlOptions['class'] = $this->getRRuleCssClass(RRule::rrTSTART);
        $htmlOptions['name'] = $this->getRRuleElemName(RRule::rrTSTART);
        $htmlOptions['id'] = $this->getRRuleElemId(RRule::rrTSTART);

        $options['language'] = $this->language;

        $this->widget('ext.recur.extensions.jui_timepicker.JTimePicker', array(
            'model' => $this->model,
            'attribute' => RRule::rrTSTART,
            'language' => $this->getLanguage(),
            'theme' => $this->theme,
            'themeUrl' => $this->themeUrl,
            'options' => $options,
            'htmlOptions' => $htmlOptions,
        ));
    }


    /**
     * Render the end time TEND as JTimePicker
     *
     * @param array $options
     * @param array $htmlOptions
     */
    public function renderTEnd($options = array(), $htmlOptions = array('size' => 5, 'maxlength' => 5))
    {
        $htmlOptions['class'] = $this->getRRuleCssClass(RRule::rrTEND);
        $htmlOptions['name'] = $this->getRRuleElemName(RRule::rrTEND);
        $htmlOptions['id'] = $this->getRRuleElemId(RRule::rrTEND);

        $options['language'] = $this->language;

        $this->widget('ext.recur.extensions.jui_timepicker.JTimePicker', array(
            'model' => $this->model,
            'attribute' => RRule::rrTEND,
            'language' => $this->getLanguage(),
            'theme' => $this->theme,
            'themeUrl' => $this->themeUrl,
            'options' => $options,
            'htmlOptions' => $htmlOptions,
        ));
    }


    /**
     * Render the UNTIL date as CJuiDatePicker
     *
     * @param array $options
     * @param array $htmlOptions
     */
    public function renderRRuleUntil($options = array(), $htmlOptions = array())
    {
        $htmlOptions['class'] = $this->getRRuleCssClass(RRule::rrUNTIL);
        $htmlOptions['name'] = $this->getRRuleElemName(RRule::rrUNTIL);
        $htmlOptions['id'] = $this->getRRuleElemId(RRule::rrUNTIL);

        if ($this->model->COUNT > 0)
            $htmlOptions['disabled'] = true;

        $this->widget('zii.widgets.jui.CJuiDatePicker', array(
            'model' => $this->model,
            'attribute' => RRule::rrUNTIL,
            'language' => $this->getLanguage(),
            'theme' => $this->theme,
            'themeUrl' => $this->themeUrl,
            'options' => $options,
            'htmlOptions' => $htmlOptions,
        ));
    }


    /*
     * Get the url for ajax requests to the RRuleFormAction
     */
    public function getAjaxActionUrl($action, $urlParams = array())
    {
        $params = array_merge($urlParams, array('action' => $action,
            'wid' => $this->id,
            'fview' => $this->formView,
            'rview' => $this->ruleSummaryView,
            'calview' => $this->calendarView,
            'theme' => $this->theme,
            't' => $this->timeEnabled,
            'cid' => $this->getActionControllerId(),
        ));

        return Yii::app()->createUrl($this->getActionControllerId() . '/rruleform', $params);
    }

    /**
     * Get a ajax action button to call actions from the RRuleFormAction
     *
     * @param $label
     * @param $action
     * @param array $ajaxOptions
     * @param array $htmlOptions
     * @param bool $return
     * @return string
     */
    public function ajaxActionButton($label, $action, $ajaxOptions = array(), $htmlOptions = array(), $return = false)
    {
        $url = $this->getAjaxActionUrl($action);

        $ajaxOptions['update'] = '#' . $this->messageContainerId;

        $button = CHtml::ajaxSubmitButton($label, $url, $ajaxOptions, $htmlOptions);

        if ($return)
            return $button;
        else
            echo $button;
    }


    /**
     * Get an icon from the assets folder
     *
     * @param $icon
     * @param string $alt
     * @param array $htmlOptions
     * @return string
     */
    public function getActionIcon($icon, $alt = '',$htmlOptions = array())
    {
        $assets = $this->getAssets();
        $src = $assets . "/images/$icon.png";
        return CHtml::image($src, $alt, $htmlOptions);
    }

    /**
     * Get the icon for a ajax action
     *
     * @param $ajaxAction
     * @param string $alt
     * @param array $htmlOptions
     * @return string
     */
    protected function ajaxActionIcon($ajaxAction, $alt = '', $htmlOptions = array())
    {
        switch ($ajaxAction)
        {
            case "listpreviewrule":
                $result = $this->getActionIcon('view', $alt, $htmlOptions);
                break;
            case "calpreviewrule":
                $result = $this->getActionIcon('calendar', $alt, $htmlOptions);
                break;
            case "ruleupdate":
                $result = $this->getActionIcon('update', $alt, $htmlOptions);
                break;
            default:
                $result = $ajaxAction;
                break;
        }

        return $result;
    }

    /**
     * Get a ajax action icon to call actions from the RRuleFormAction
     *
     * @param $action
     * @param array $urlParams
     * @param array $htmlOptions
     * @return string
     */
    public function getAjaxActionIcon($action, $urlParams = array(), $alt = '', $htmlOptions = array())
    {
        $url = $this->getAjaxActionUrl($action, $urlParams);
        $containerId = $this->messageContainerId;

        $icon = $this->ajaxActionIcon($action,$alt);

        //cannot use CHtml, because this registers a script
        $onClick = <<<EOP
$.ajax({
  type: 'get',
  cache: false,
  url: '$url',
  success: function(response){
    $('#$containerId').html(response);
  }
});
EOP;

        $htmlOptions['onclick'] = $onClick;

        $link = CHtml::link($icon, '#', $htmlOptions);

        return $link;
    }

    /**
     * Get the action icon for deleting a rule from the rulecontainer
     *
     * @param $removeConfirmText
     * @return string
     */
    public function getRemoveActionIcon($removeConfirmText,$alt='')
    {
        $icon = $this->getActionIcon('delete',$alt);
        $onClick = 'if(confirm("' . $this->t($removeConfirmText) . '")) $(this).parent().parent().remove(); return false;';
        return CHtml::link($icon, '#', array('onclick' => $onClick));
    }

    /**
     * Get the id of the controller that handles the ajax requests
     * @see RRuleFormAction.php
     *
     * @return string
     */
    public function getActionControllerId()
    {
        return $this->controller->id;
    }

    /**
     * Run the widget: display the rules container
     */
    public function run()
    {
        echo $this->getRulesContainer();
    }

    /**
     * Init the input elements and bind the onchange events
     */
    public function registerElemOnChangeScript()
    {
        $script = "onFreqChange(0,'{$this->id}');";

        $prefixByDayId = $this->getRRuleElemId(RRule::rrPrefixBYDAY);
        $script .= "$('#$prefixByDayId').bind('multiselectclose', function(event, ui){onPrefixByDayClose('{$this->id}')});";

        $byMonthDayId = $this->getRRuleElemId(RRule::rrBYMONTHDAY);
        $script .= "$('#$byMonthDayId').bind('multiselectclose', function(event, ui){onPrefixByMonthDayClose('{$this->id}')});";

        CHtml::script($script);
        Yii::app()->getClientScript()->registerScript($this->id . 'initIpts', $script, CClientScript::POS_READY);
    }


    /**
     * Render the recurring rule form
     */
    public function renderForm()
    {
        $this->render($this->formView);

        //init the formelements and bind the onchange events: must be the last registered scripts
        $this->registerElemOnChangeScript();
    }



    /**
     * Get the id of the rules container
     * @return string
     */
    public function getRulesContainerId()
    {
        return $this->id . '-crules';
    }

    /**
     * Get the id of the message container (previews, errormessages)
     * @return string
     */
    public function getMessageContainerId()
    {
        return $this->id . '-cmsg';
    }


    /**
     * Register the translation source in 'ext.recur.messages'
     * @static
     */
    public static function registerTranslationSource()
    {
        if(!Yii::app()->hasComponent('rrulemessages'))
            Yii::app()->setComponents(
                array('rrulemessages' => array(
                    'class' => 'CPhpMessageSource',
                    'basePath' => 'protected/extensions/recur/messages',
                )));
    }


}

RRuleForm::registerTranslationSource();