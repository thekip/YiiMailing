<?php

class DebugWidget extends CWidget {

    public function run() {
        if (Yii::app()->user->hasFlash('emailDebug')) {
            //register css file
            $url = CHtml::asset(Yii::getPathOfAlias('ext.Mailing.css.debug') . '.css');
            Yii::app()->getClientScript()->registerCssFile($url);

            //dump debug info
            foreach (Yii::app()->user->getFlash('emailDebug') as $email) {
                echo $email;
            };
        }
    }

}