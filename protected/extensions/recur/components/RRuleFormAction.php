<?php
/**
 * RRuleFormAction.php
 *
 * A CAction that handles the ajax requests from then RRuleForm widget
 * Add this action to the controller that uses the widget
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

Yii::import('ext.recur.models.*');
Yii::import('ext.recur.components.RRuleForm');

class RRuleFormAction extends CAction
{
    /**
     * The messages for the javascript alerts
     * The text will be translated by RRuleForm::t
     * Add the translation to the messages folder of this extension
     *
     * @var string
     */
    public $ruleRemoveConfirmText = 'Delete this rule?';
    public $ruleExistsText = 'This rule already exists';

    /**
     * The messageresponse if not datetimes are available to display
     * The text will be translated by RRuleForm::t
     * Add the translation to the messages folder of this extension
     *
     * @var string
     */
    public $emptyDateTimesText = 'No datetimes available';

    /**
     * The format of the datetimes in the list preview
     * Use CDateFormatter syntax here
     * @var string
     */
    public $datePreviewFormat = "EEEE, d. MMM yy";
    public $datetimePreviewFormat = 'EEEE, d. MMM yy HH:mm';
    public $timePreviewFormat = 'HH:mm';

    /*
     * Extract the attribute id for the RRuleForm widget from the ajax request
     */
    protected function getWidgetId()
    {
        return !empty($_GET['wid']) ? $_GET['wid'] : 'rrf';
    }

    /*
     * Extract the attribute view for the RRuleForm widget from the ajax request
     */
    protected function getFormView()
    {
        return !empty($_GET['fview']) ? $_GET['fview'] : 'rruleform';
    }


    /*
    * Extract the attribute view for the RRuleForm widget from the ajax request
    */
    protected function getRuleSummaryView()
    {
        return !empty($_GET['rview']) ? $_GET['rview'] : 'rrulesummary';
    }

    /*
    * Extract the attribute view for the RRuleForm widget from the ajax request
    */
    protected function getCalendarView()
    {
        return !empty($_GET['calview']) ? $_GET['calview'] : 'fullcalendar';
    }

    /*
    * Extract the attribute view for the RRuleForm widget from the ajax request
    */
    protected function getTheme()
    {
        return !empty($_GET['theme']) ? $_GET['theme'] : 'basic';
    }

    /*
     * Extract the attribute timeEnabled for the RRuleForm widget from the ajax request
     */
    public function getTimeEnabled()
    {
        return !empty($_GET['t']) ? $_GET['t'] : false;
    }

    /*
    * Extract the attributes for the RRuleForm widget
    */
    protected function getRRuleFormAttributes($model = null)
    {
        return array(
            'model' => $model,
            'formView' => $this->getFormView(),
            'ruleSummaryView' => $this->getRuleSummaryView(),
            'calendarView' => $this->getCalendarView(),
            'theme' => $this->getTheme(),
            'id' => $this->getWidgetId(),
            'timeEnabled' => $this->getTimeEnabled(),
        );
    }

    /**
     * Create a RRuleForm and populate the attributes with the submitted values
     *
     * @param null $model
     * @return RRuleForm
     */
    protected function createRRuleForm($model = null)
    {
        $widget = new RRuleForm($this->controller);

        foreach ($this->getRRuleFormAttributes($model) as $name => $value)
            $widget->$name = $value;

        return $widget;
    }

    // datepickers... doesn't work on ajax-response
    /* protected function renderRuleUpdate()
    {
        $model = new RRule();
        $model->setRRuleformAction($this);
        $model->rule = isset($_GET['rule']) ? $_GET['rule'] : '';


        $widget = new RRuleForm();
        $widget->registerTranslationSource();

        $this->controller->renderPartial('ext.recur.components.views.rruleform',array('model'=>$model,'this'=>$widget), false,true);

        //$this->controller->widget('RRuleForm', $this->getRRuleFormOptions($model));
    }*/


    /**
     * Render the datetimes preview for a single rule
     */
    protected function renderRuleListPreview()
    {
        try
        {
            $model = new RRule();

            $widgetId = $this->getWidgetId();
            $htmlOptions = array('size' => 30,'style'=>'width:100%');

            if (isset($_GET['rule'])) //init from the submitted GET rule string
            {
                if (($rulestr = $model->setRulestr($_GET['rule'])))
                    echo CHtml::listBox('listpreviewrule', null, $model->getPreviewListData($this->datetimePreviewFormat, $this->datePreviewFormat, $this->timePreviewFormat), $htmlOptions);
                else
                    echo $this->validationErrorMessage($model);
            }
            else //init from the submitted form post values
            {
                if (($rulestr = $model->initFromForm($widgetId)))
                    echo CHtml::listBox('rulePreview', null, $model->getPreviewListData($this->datetimePreviewFormat, $this->datePreviewFormat, $this->timePreviewFormat), $htmlOptions);
                else
                    echo $this->validationErrorMessage($model);
            }
        }
        catch (Exception $e)
        {
            $msg = 'Error: ' . $e->getMessage();
            Yii::log($msg, CLOGGER::LEVEL_ERROR);
            echo $msg;
        }
    }


    /**
     * Render the datetimes preview for the rules
     */
    protected function renderFullCalendarRulePreview()
    {
        try
        {
            $model = new RRule();
            $widgetId = $this->getWidgetId();

            if (isset($_GET['rule'])) //init from the submitted GET rule string
            {
                if (($rulestr = $model->setRulestr($_GET['rule'])))
                {
                    $this->renderCalendarView($model);
                }
                else
                    echo $this->validationErrorMessage($model);
            }
            else //init from the submitted form post values
            {
                if (($rulestr = $model->initFromForm($widgetId)))
                {
                    $this->renderCalendarView($model);
                }
                else
                    echo $this->validationErrorMessage($model);
            }
        }
        catch (Exception $e)
        {
            $msg = 'Error: ' . $e->getMessage();
            Yii::log($msg, CLOGGER::LEVEL_ERROR);
            echo $msg;
        }
    }

    /**
     * Internal rendering of the calendarview
     * @param $model
     */
    protected function renderCalendarView($model)
    {
        $events = $model->getFullCalendarEvents();

        if (empty($events))
            echo RRuleForm::t($this->emptyDateTimesText);
        else
        {
            $widget = $this->createRRuleForm();
            $widget->render($this->getCalendarView(), array('events' => $events));
        }
    }


    /**
     * Render the datetimes preview for the rules
     */
    protected function renderRulesListPreview()
    {
        try
        {
            $rrules = RRules::createFromPOST();

            if ($rrules->hasErrors())
            {
                $msg = CHtml::errorSummary($rrules);
                echo CHtml::tag('div', array('class' => 'flash-error'), $msg);
            }
            else
            {
                $data = $rrules->getPreviewListData($this->getTimeEnabled(), $this->datetimePreviewFormat, $this->datePreviewFormat, $this->timePreviewFormat);
                if (empty($data))
                    echo RRuleForm::t($this->emptyDateTimesText);
                else
                {
                    $htmlOptions = array('size' => 30);
                    echo CHtml::listBox('rulePreview', null, $data, $htmlOptions);
                }
            }
        }
        catch (Exception $e)
        {
            $msg = 'Error: ' . $e->getMessage();
            Yii::log($msg, CLOGGER::LEVEL_ERROR);
            echo $msg;
        }
    }

    /**
     * Render the datetimes preview for the rules
     */
    protected function renderFullCalendarRulesPreview()
    {
        try
        {
            $rrules = RRules::createFromPOST();

            if ($rrules->hasErrors())
            {
                $msg = CHtml::errorSummary($rrules);
                echo CHtml::tag('div', array('class' => 'flash-error'), $msg);
            }
            else
            {
                $this->renderCalendarView($rrules);
            }
        }
        catch (Exception $e)
        {
            $msg = 'Error: ' . $e->getMessage();
            Yii::log($msg, CLOGGER::LEVEL_ERROR);
            echo $msg;
        }
    }


    /**
     * Render the javascript code for adding a rule to the rulescontainer
     */
    protected function renderAddRule()
    {
        $widgetId = $this->getWidgetId();

        $model = new RRule();

        if (($rulestr = $model->initFromForm($widgetId)))
        {
            $rrForm = $this->createRRuleForm($model);
            $removeIcon = $rrForm->getActionIcon('delete');

            $summary = $this->controller->renderPartial('ext.recur.components.views.' . $this->getRuleSummaryView(), array('model' => $model), true, true);

            $formOptions = $this->getRRuleFormAttributes();
            $formOptions['rulesContainerId'] = $rrForm->getRulesContainerId();
            $formOptions = CJavaScript::encode($formOptions);

            $actionIcons = CJavaScript::encode(array(
                'remove' => $rrForm->getRemoveActionIcon($this->ruleRemoveConfirmText),
                //'update'=>$rrForm->getAjaxActionIcon('ruleupdate'),
                'listpreview' => $rrForm->getAjaxActionIcon('listpreviewrule', array('rule' => $rulestr)),
                'calpreview' => $rrForm->getAjaxActionIcon('calpreviewrule', array('rule' => $rulestr)),
            ));

            $script = "rruleAddToContainer($formOptions,$actionIcons,'$rulestr','$summary','{$this->ruleExistsText}');";

            echo CHtml::script($script);
        }
        else
            echo $this->validationErrorMessage($model);

    }


    /**
     * Return the errorSummary from a RRule/RRules model
     *
     * @param $model
     * @return string
     */
    protected function validationErrorMessage($model)
    {
        $msg = $model->hasErrors() ? CHtml::errorSummary($model) : RRuleForm::t('Invalid rule');
        return CHtml::tag('div', array('class' => 'flash-error'), $msg);
    }

    /**
     * Run the actions that handles the ajax response
     */
    public function run()
    {
        if (Yii::app()->request->isAjaxRequest)
        {
            $action = isset($_GET['action']) ? $_GET['action'] : null;

            if (empty($action))
            {
                echo 'Param missing: action';
                Yii::app()->end();
            }

            switch ($action)
            {

                /*  case 'ruleupdate':
                $this->renderRuleUpdate();
                break;*/

                case 'listpreviewrule':
                    $this->renderRuleListPreview();
                    break;

                case 'listpreviewrules':
                    $this->renderRulesListPreview();
                    break;

                case 'calpreviewrule':
                    $this->renderFullCalendarRulePreview();
                    break;

                case 'calpreviewrules':
                    $this->renderFullCalendarRulesPreview();
                    break;

                case 'addrule':
                    $this->renderAddRule();
                    break;

                default:
                    echo 'Unknown action: ' . $action;
            }

            Yii::app()->end();
        }
    }
}
