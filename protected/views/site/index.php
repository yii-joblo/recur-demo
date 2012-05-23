<?php $this->pageTitle=Yii::app()->name; ?>

<h1>Demoapplication for the Yii extension "<i><?php echo CHtml::encode(Yii::app()->name); ?></i>"</h1>

Add rules for recurring datetimes to the event below.
<br/>
<br/>

<?php
$this->renderPartial('eventform',array('rrules'=>$rrules));
