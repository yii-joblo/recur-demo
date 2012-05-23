<?php
/**
 * Display the form for generating a single RRule model
 * You can copy and modify this code to generate a customized form.
 * You have to register the customized form by setting RRuleForm::formView to the new filename
 *
 * $this is the RRuleForm widget
 */

$this->widget('ext.recur.extensions.slidetoggle.ESlidetoggle',
    array(
        'itemSelector' => 'div.form.rruleform',
        'titleSelector' => 'span.rruleform-caption',
        'collapsed' => 'div.form.rruleform', //uncomment to show all collapsed
    ));
?>
<div class="form rruleform">
    <span class="rruleform-caption">Add/Exclude datetimes</span>
    <fieldset class="rrule">
        <?php echo CHtml::beginForm(); ?>
        <div>
            <?php echo $this->renderExRule(array('0'=>'Repeat','1'=>'Exclude'),array('separator'=>' ')); ?>
            <?php echo CHtml::label('Every', null); ?>
            <?php echo $this->renderRRuleInterval(); ?>
            <?php echo $this->renderRRuleFreq(); ?>
        </div>
        <div>
            <?php echo CHtml::label('From date', null); ?>
            <?php echo $this->renderDStart(); ?>
            <?php if ($this->timeEnabled)
        {
            echo CHtml::label('time', null);
            echo $this->renderTStart(array(
                'showPeriodLabels' => false
            ));
            echo CHtml::label('-', null);
            echo $this->renderTEnd(array(
                'showPeriodLabels' => false
            ));
        }
            ?>
            <?php echo CHtml::label('Only in the month', null); ?>
            <?php echo $this->renderRRuleByMonth(array(),
            array(
                'separator' => '',
                'template' => '<span>{input}&nbsp;{label}</span>'
            ));
            ?>
        </div>
        <div>
            <?php echo CHtml::label('until', null); ?>
            <?php echo $this->renderRRuleUntil(); ?>
            <?php echo CHtml::label('ends after', null); ?>
            <?php echo $this->renderRRuleCount(); ?>
            <?php echo CHtml::label('dates', null); ?>
        </div>
        <div>
            <?php echo CHtml::label('Days', null); ?>
            <?php echo $this->renderRRulePrefixByDay('Every',
              array(), array(
                'separator' => '',
                'template' => '<span>{input}&nbsp;{label}</span>'
            ));
            ?>
            <?php echo CHtml::label('on', null); ?>
            <?php echo $this->renderRRuleByDay('-',
            array(), array(
                'separator' => '',
                'template' => '<span>{input}&nbsp;{label}</span>'
            ));
            ?>

            <?php echo CHtml::label('Day of month', null); ?>
            <?php echo $this->renderRRuleByMonthDay(array(), array(
            'separator' => '',
            'template' => '<span>{input}&nbsp;{label}</span>'
        ));
            ?>
        </div>

        <div class="row clear action">
            <?php $this->ajaxActionButton('List preview', 'listpreviewrule');?>
            <?php $this->ajaxActionButton('Calendar preview', 'calpreviewrule');?>
            <?php $this->ajaxActionButton('Add rule', 'addrule');?>
        </div>

        <?php echo CHtml::endForm(); ?>

    </fieldset>
</div><!-- form rruleform -->
<br/>
<br/>
<?php $this->renderMessageContainer('',array('class'=>'row clear'));  ?>

