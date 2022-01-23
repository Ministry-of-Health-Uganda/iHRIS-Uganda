<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 *  I2CE_Module_Jumper
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */


class I2CE_Module_Jumper extends I2CE_Module {
    public static function getMethods() {
        return array(
            'I2CE_Page->makeJumper'=>'makeJumper',
            'I2CE_Template->makeJumper'=>'makeJumper',
            'I2CE_Page->makeScalingJumper'=>'makeScalingJumper',
            'I2CE_Template->makeScalingJumper'=>'makeScalingJumper'

            );
    }



    /**
     * Shows the jumper.  This jumper works for pages that process a get request.  The page must put the all results in
     * the node with id "{$id_base}_results".   The jumper is placed in the node with id "{$id_base}_pager_display".  It
     * is expected that the node with id "{$id_base}_pager_display" is a sub-node of the node with id "{$id_base}_results".
     * Note:  there will be several nodes created with id's of the form "{$id_base}_pager_$something" so with the exception
     * of $something='display' you should not use id's of this form.  
     *
     * This fuzzy method can be called either from an I2CE_Page or an I2CE_Template.
     *
     * @param string $id_base the base id that should be used in identifying the node to update (e.g. 'report')
     * @param array $jumps array of int, the pages we want in the jumper.  Does not inclu
     * @param int $page the current page number
     * @param int $total_pages  the total numnber pf pages
     * @param string $pageURL the url of the page we will make the request from
     * @param associatibe array $query_fields  keys are the query variables needed for the get request.  values are the _unencoded_ values that
     * the variable should have.  
     * @param string $pageVaraiable the get variable to store the requested page number in.  Defaults to 'page'
     */
    public function _makeJumper($template,$id_base, $jumps, $page,$total_pages,$pageURL,$query_fields, $pageVariable='page') {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }        
        $template->addHeaderLink("jumper.css");
        if (!is_array($query_fields)) {
            I2CE::raiseError("Display jumper expects $query_fields to be an array.  Got: " . print_r($query_fields,true));
        }
        $qry_string = '';
        $query_fields = I2CE_Page::flattenRequestVars($query_fields);
        foreach ($query_fields as $name=>$value) {
            $qry_string .=  $name . '=' . urlencode($value) . '&';
        }
        if (strpos($pageURL,'?') !== false) {
            if ($qry_string) {
                $page_root =  $pageURL .'&' .  $qry_string .   $pageVariable . '=';
            } else {
                $page_root =  $pageURL .  '&' . $pageVariable . '=';
            }
        } else {
            if ($qry_string) {
                $page_root =  $pageURL . '?' .  $qry_string .  $pageVariable . '=';
            } else {
                $page_root =  $pageURL . '?' . $pageVariable . '=';
            }
        }
        if (!in_array(1,$jumps)) {
            $rewind_node = $template->appendElementById( 
                    "{$id_base}_pager_display",  
                    "a", 
                    array( "href" => $page_root . '1' ,
                           'id'=>"{$id_base}_pager_rewind_page",
                            ) 
                    );
            $template->appendElementByNode( $rewind_node, 
                                            "img",
                                            array( "src" => 'file/rewind.gif',
                                                   "height" => 7, 
                                                   "width" => 9, 
                                                   "border" => 0 
                                                ) 
                );
            $prev_node = $template->appendElementById( "{$id_base}_pager_display", 
                                                       "a",
                                                       array( "href" => $page_root .  ($page - 1)  ,
                                                              'id'=>"{$id_base}_pager_prev_page"
                                                           ) 
                );
            $template->appendElementByNode( $prev_node, 
                                            "img",
                                            array( "src" => 'file/prev.gif',
                                                   "height" => 7, 
                                                   "width" => 6, 
                                                   "border" => 0 
                                                ) 
                );
            if ($template->hasAjax()) {
                $template->addAjaxUpdate("{$id_base}_results",
                                         "{$id_base}_pager_rewind_page",
                                         'click', 
                                         $page_root . '1',
                                         "{$id_base}_results",true); 
                $template->addAjaxUpdate("{$id_base}_results",
                                         "{$id_base}_pager_prev_page",
                                         'click', 
                                         $page_root .  ($page -1) ,
                                         "{$id_base}_results",true); 
            }
        }
        foreach( $jumps as $jump ) {
            if ($jump < 1 || $jump > $total_pages) {
                continue;
            }
            if ( $jump == $page ) {
                $template->appendElementById( "{$id_base}_pager_display", 
                                              "span",
                                              array( "class" => "pager_current" ), 
                                              $jump );
            } else {
                $template->appendElementById( "{$id_base}_pager_display", 
                                              "a",                                                                    
                                              array( "href" => $page_root .  $jump,
                                                     'id'=>"{$id_base}_pager_" . $jump . "_page"
                                                  ), 
                                              $jump 
                    );
                if ($template->hasAjax()) {
                    $template->addAjaxUpdate("{$id_base}_results",
                                             "{$id_base}_pager_" . $jump . "_page",
                                             'click', 
                                             $page_root . $jump,
                                             "{$id_base}_results",true); 
                }
            }
        }
        if (!in_array($total_pages,$jumps)) {
            $next_node = $template->appendElementById( "{$id_base}_pager_display", 
                                                       "a",
                                                       array( "href" => $page_root .  ($page+1),
                                                              "id"=> "{$id_base}_pager_next_page"
                                                           ) 
                );
            $template->appendElementByNode( $next_node, 
                                            "img",
                                            array( "src" => 'file/next.gif',
                                                   "height" => 7, 
                                                   "width" => 6, 
                                                   "border" => 0 
                                                ) 
                );
            $ff_node = $template->appendElementById( "{$id_base}_pager_display", 
                                                     "a",
                                                     array( "href" => $page_root . $total_pages,
                                                            "id"=> "{$id_base}_pager_fast_forward_page"
                                                         ) 
                );
            $template->appendElementByNode( $ff_node, 
                                            "img",
                                            array( "src" =>   'file/fastforward.gif',
                                                   "height" => 7, 
                                                   "width" => 9, 
                                                   "border" => 0 
                                                ) 
                );
            if ($template->hasAjax()) {
                $template->addAjaxUpdate("{$id_base}_results",
                                         "{$id_base}_pager_fast_forward_page",
                                         'click', 
                                         $page_root . $total_pages,   
                                         "{$id_base}_results",true); 
                $template->addAjaxUpdate("{$id_base}_results",
                                         "{$id_base}_pager_next_page",
                                         'click', 
                                         $page_root  . ($page +1),    
                                         "{$id_base}_results",true); 
            }
        }
    }

    /**
     * Shows the jumper.  This jumper works for pages that process a get request.  The page must put the all results in
     * the node with id "{$id_base}_results".   The jumper is placed in the node with id "{$id_base}_pager_display".  It
     * is expected that the node with id "{$id_base}_pager_display" is a sub-node of the node with id "{$id_base}_results".
     * Note:  there will be several nodes created with id's of the form "{$id_base}_pager_$something" so with the exception
     * of $something='display' you should not use id's of this form.  
     *
     * This fuzzy method can be called either from an I2CE_Page or an I2CE_Template.
     *
     * @param string $id_base the base id that should be used in identifying the node to update (e.g. 'report')
     * @param int $page the current page number
     * @param int $total_pages  the total numnber pf pages
     * @param string $pageURL the url of the page we will make the request from
     * @param associatibe array $query_fields  keys are the query variables needed for the get request.  values are the _unencoded_ values that
     * the variable should have.  
     * @param string $pageVaraiable the get variable to store the requested page number in.  Defaults to 'page'
     */
    public function makeJumper($template,$id_base, $page,$total_pages,$pageURL,$query_fields, $pageVariable='page') {
        $jump_start = $page - 2; 
        if ( $jump_start < 1 ) {
            $jump_start = 1;
        }
        $jump_end = $jump_start + 4;
        while ( $jump_end > $total_pages ) {
            $jump_end--;
            $jump_start--;
        }
        if ( $jump_start < 1 ) {
            $jump_start = 1;
        }
        $jumps = range( $jump_start, $jump_end );
        $this->_makeJumper($template,$id_base,$jumps,$page,$total_pages,$pageURL,$query_fields,$pageVariable);
    }




    /**
     * Shows the jumper.  This jumper works for pages that process a get request.  The page must put the all results in
     * the node with id "{$id_base}_results".   The jumper is placed in the node with id "{$id_base}_pager_display".  It
     * is expected that the node with id "{$id_base}_pager_display" is a sub-node of the node with id "{$id_base}_results".
     * Note:  there will be several nodes created with id's of the form "{$id_base}_pager_$something" so with the exception
     * of $something='display' you should not use id's of this form.  
     *
     * This fuzzy method can be called either from an I2CE_Page or an I2CE_Template.
     *
     * @param string $id_base the base id that should be used in identifying the node to update (e.g. 'report')
     * @param int $page the current page number
     * @param int $total_pages  the total numnber pf pages
     * @param string $pageURL the url of the page we will make the request from
     * @param associatibe array $query_fields  keys are the query variables needed for the get request.  values are the _unencoded_ values that
     * the variable should have.  
     * @param string $pageVaraiable the get variable to store the requested page number in.  Defaults to 'page'
     */
    public function makeScalingJumper($template,$id_base, $page,$total_pages,$pageURL,$query_fields, $pageVariable='page') {
        $jump_start = $page - 3; 
        if ( $jump_start < 1 ) {
            $jump_start = 1;
        }
        $jump_end = $page + 3;
        while ( $jump_end > $total_pages ) {
            $jump_end--;
            $jump_start--;
        }
        if ( $jump_start < 1 ) {
            $jump_start = 1;
        }
        $jumps = range( $jump_start, $jump_end );
        $scale = 10;
        $jump_end += $scale;
        $count = 0;
        while ($jump_end < $total_pages) {
            $jumps[] = $jump_end;
            if ($count == 2) {
                $count = 0;
                $scale *= 10;
            } else {
                $count++;
            } 
            $jump_end += $scale;
        }
        $scale = 10;
        $jump_start -= $scale;
        while($jump_start > 0) {
            array_unshift($jumps,$jump_start);
            if ($count == 2) {
                $count = 0;
                $scale *= 10;
            } else {
                $count++;
            } 
            $jump_start -= $scale;
        }
        $this->_makeJumper($template,$id_base,$jumps,$page,$total_pages,$pageURL,$query_fields,$pageVariable);
    }
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
