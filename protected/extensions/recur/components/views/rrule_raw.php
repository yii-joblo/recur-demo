<?php
/**
 * Display the raw rule string in the rulesContainer for debugging purpose
 *
 * Set this view in the widget property RRuleForm::ruleSummaryView
 * Don't use $this in this view, because it can be the RRuleForm widget or the action controller
 *
 * $model is an instance of the RRule model
 */

echo $model->getRuleStr();
