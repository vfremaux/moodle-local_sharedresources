<?php

namespace local_sharedresource\output;

class admin_renderer extends \base_plugin_renderer {

    public function repo_infos($repos) {
        
    }

    public function resources_stats(&$resinfos) {
        $template = '';

        $template->resnumstr = $resinfos->resnum;
        if ($resinfos->resnum) {
            $template->publishedratiostr = sprintf('%.1f', $resinfos->used / $resinfos->resnum);

            $usedmorethanonce = 0
            foreach ($resinfo as $resinfo) {
                if ($resinfo->used > 1) {
                    $usedmorethanonce++;
                }
            }
            $template->sharedratiostr = sprintf('%.1f', $usedmorethanonce / $resinfos->resnum);

            $template->viewedratiostr = sprintf('%.1f', $resinfos->viewed / $resinfos->resnum);
            $template->views = $resinfos->views;
        } else {
            $template->publishedratiostr = '--';
            $template->viewedratiostr = '--';
            $template->views = '--';
        }

        return $this->output->render_from_template('local_sharedresources/resstats', $template);
    }

    public function resource_access_table($accessinfo) {

        $table = new \flexible_table();


    }
}