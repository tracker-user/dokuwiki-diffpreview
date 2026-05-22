<?php

/**
 * Helper component of the DiffPreview plugin.
 *
 * Renders the "changes" view: the edit form followed by a diff of the unsaved
 * edits against the saved page. Used on DokuWiki release Igor and above, where
 * the diff UI moved to dokuwiki\Ui\PageDiff.
 *
 * Loaded via `new helper_plugin_diffpreview_changes` from action.php; the
 * class name and helper/ file path follow DokuWiki's helper-autoload
 * convention (this is what GitHub issue #5 / release 1.3.1 addressed).
 *
 * Local fork modifications vs upstream 1.3.1 (2023-06-18):
 *   - Bug fix: tplContent() referenced $INFO['id'] but never declared
 *     `global $INFO`, so $INFO was an undefined variable -> null -> two PHP 8
 *     warnings ("Undefined variable $INFO" and "array offset on null") on
 *     every preview. PageDiff() with no argument resolves the current page id
 *     itself, so the explicit (and broken) $INFO['id'] argument is dropped.
 *   - Removed dead code: the constructor plus the minimumPermission(),
 *     checkPreconditions(), preProcess() and getActionName() methods and the
 *     $preview property were scaffolding for an unrealised "Action\Changes"
 *     design. Nothing ever called them, and the constructor needlessly
 *     instantiated dokuwiki\Action\Preview on every preview. Only
 *     tplContent() is actually used (by action.php).
 *   - Corrected the base class name casing (DokuWiki_Plugin).
 *   See README.md.
 */
class helper_plugin_diffpreview_changes extends DokuWiki_Plugin
{
    /**
     * Output the edit form followed by a diff preview of the current changes.
     */
    public function tplContent()
    {
        global $PRE, $TEXT, $SUF;

        (new dokuwiki\Ui\Editor())->show();
        echo '<br id="scroll__here" />';

        (new dokuwiki\Ui\PageDiff())
            ->compareWith(con($PRE, $TEXT, $SUF))
            ->preference(['showIntro' => true, 'difftype' => 'sidebyside'])
            ->show();
    }
}
