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
    <span class="rruleform-caption">Termine hinzufügen/ausschließen</span>
    <fieldset class="rrule">
        <?php echo CHtml::beginForm(); ?>
        <div>
            <?php echo $this->renderExRule(array('0'=>'Wiederhole','1'=>'Schließe aus'),array('separator'=>' ')); ?>
            <?php echo CHtml::label('Jede(n/s)', null); ?>
            <?php echo $this->renderRRuleInterval(); ?>
            <?php echo $this->renderRRuleFreq(); ?>
        </div>
        <div>
            <?php echo CHtml::label('Ab Datum', null); ?>
            <?php echo $this->renderDStart(); ?>
            <?php if ($this->timeEnabled)
                            {
                                echo CHtml::label('Zeit', null);
                                echo $this->renderTStart(array(
                                    'showPeriodLabels' => false
                                ));
                                echo CHtml::label('-', null);
                                echo $this->renderTEnd(array(
                                    'showPeriodLabels' => false
                                ));
                            }
            ?>
            <?php echo CHtml::label('Nur in den Monaten', null); ?>
            <?php echo $this->renderRRuleByMonth(array(
                'checkAllText'=> 'Alle auswählen',
                'uncheckAllText'=> 'Auswahl entfernen',
            ),
            array(
                'separator' => '',
                'template' => '<span>{input}&nbsp;{label}</span>'
            ));
            ?>
        </div>
        <div>
            <?php echo CHtml::label('Bis zum', null); ?>
            <?php echo $this->renderRRuleUntil(); ?>
            <?php echo CHtml::label('Endet nach', null); ?>
            <?php echo $this->renderRRuleCount(); ?>
            <?php echo CHtml::label('Terminen', null); ?>
        </div>
        <div>
            <?php echo CHtml::label('Tage', null); ?>
            <?php echo $this->renderRRulePrefixByDay('Jeden',
                   array(
                        'checkAllText'=> 'Alle auswählen',
                        'uncheckAllText'=> 'Auswahl entfernen',
                    ), array(
                        'separator' => '',
                        'template' => '<span>{input}&nbsp;{label}</span>'
                    ));
            ?>
            <?php echo CHtml::label('am', null); ?>
            <?php echo $this->renderRRuleByDay('-',
                                                array(
                                                 'checkAllText'=> 'Alle auswählen',
                                                 'uncheckAllText'=> 'Auswahl entfernen',
                                                ), array(
                                                    'separator' => '',
                                                    'template' => '<span>{input}&nbsp;{label}</span>'
                                                ));
            ?>

            <?php echo CHtml::label('Tag des Monats', null); ?>
            <?php echo $this->renderRRuleByMonthDay(array(
                                                'checkAllText'=> 'Alle auswählen',
                                                'uncheckAllText'=> 'Auswahl entfernen',
                                            ), array(
                                                'separator' => '',
                                                'template' => '<span>{input}&nbsp;{label}</span>'
                                            ));
            ?>
        </div>

        <div class="row clear action">
            <?php $this->ajaxActionButton('Vorschau Liste', 'listpreviewrule');?>
            <?php $this->ajaxActionButton('Vorschau Kalender', 'calpreviewrule');?>
            <?php $this->ajaxActionButton('Hinzufügen', 'addrule');?>
        </div>

        <?php echo CHtml::endForm(); ?>

    </fieldset>
</div><!-- form rruleform -->
<br/>
<br/>
<?php $this->renderMessageContainer('',array('class'=>'row clear'));  ?>

