<div class="form">
<?php

echo CHtml::errorSummary($rrules, 'Invalid rules');
echo CHtml::beginForm();
?>
<div class="row">
<?php
echo CHtml::label('Event title', null);
echo CHtml::textField('eventtitle');
?>
</div>
<div class="row">
<?php
echo CHtml::label('Event subtitle', null);
echo CHtml::textField('eventsubtitle');
?>
</div>
<?php
//render the rules container
$recurWidget = $this->widget('ext.recur.components.RRuleForm',
    array(
        //'theme' => 'cupertino',
        'rrules' => $rrules,
    )
);

$recurWidget->renderRulesListPreviewButton();
$recurWidget->renderRulesCalPreviewButton();

echo CHtml::submitButton('Submit');

echo CHtml::endForm();
?>
</div>
<br/>
<?php

$recurWidget->renderForm();




