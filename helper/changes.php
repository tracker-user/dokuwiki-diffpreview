<?php

/**
 * How dokuwiki\Action\Changes would look like if it existed in Dokuwiki release Igor.
 * This class is mostly a wrapper around dokuwiki\Action\Preview.
 * 
 * NB: This class is currently unused.
 */
class helper_plugin_diffpreview_changes extends Dokuwiki_Plugin {
    protected $actionname = 'changes';
    protected $preview;

    public function __construct() {
        $this->preview = new dokuwiki\Action\Preview;
    }

    public function minimumPermission() {
        return $this->preview->minimumPermission();
    }
    
    public function checkPreconditions() {
        return $this->preview->checkPreconditions();
    }
    
    public function preProcess() {
        // This will save a draft
        return $this->preview->preProcess();
    }
    
    public function tplContent() {
        global $PRE, $TEXT, $SUF;

        (new dokuwiki\Ui\Editor)->show();
        echo '<br id="scroll__here" />';
        ((new dokuwiki\Ui\PageDiff($INFO['id']))
            ->compareWith(con($PRE,$TEXT,$SUF))
            ->preference(['showIntro' => true, 'difftype' => 'sidebyside'])
        )->show();
    }

    public function getActionName() {
        return $this->actionname;
    }
}
