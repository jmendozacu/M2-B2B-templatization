<?php
/**
* Hello Rewrite Product ListProduct Block
*
* @category    Webkul
* @package     Webkul_Hello
* @author      Webkul Software Private Limited
*
*/
namespace Dckap\Theme\Block\Html;

use Magento\Framework\View\Element\Template;

class Topmenu extends \Magento\Theme\Block\Html\Topmenu
{

    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_before',
            ['menu' => $this->_menu, 'block' => $this]
            );

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getHtml($this->_menu, $childrenWrapClass, $limit);

        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_after',
            ['menu' => $this->_menu, 'transportObject' => $transportObject]
            );
        $html = $transportObject->getHtml();

        return $html;
    }
    /**
    * Add sub menu HTML code for current menu item
    *
    * @param \Magento\Framework\Data\Tree\Node $child
    * @param string $childLevel
    * @param string $childrenWrapClass
    * @param int $limit
    * @return string HTML code
    */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        if (!$child->hasChildren()) {
            return $html;
        }

        $colStops = null;
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }

        if($childLevel < 1)
        {
            $html .= '<ul class="level' . $childLevel . ' submenu dckap-parent">';
            $html .= '<li class="jumbo-close"><a href="#" style="font-size: 14px;color: #696969 !important;font-family: arial;" class="close-sprint">X</a></li>';
            $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
            $html .= '</ul>';
        }else if($childLevel >= 1)
        {
            $html .= '<div class="dckap-jumbo">';

            $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
            $html .= '</div>';
        }

        return $html;
    }
    protected function _getHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        $colBrakes = []
        ) 
    {
        $html = '';
        $parent_url = '';
        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';
        $i = 0; $level1cnt = 0; $totalcnt = 0;
        if($childLevel == 1)
            $html .= '<li class="jumbo_scroll"><ul class="jumbo_sub_menu">';
        foreach ($children as $child) 
        {
            if($childLevel == 1){
                ++$level1cnt;
            }
            ++$totalcnt;
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            if (count($colBrakes) && $colBrakes[$counter]['colbrake']) {
                $html .= '</ul></li><li class="column"><ul>';
            }


            if( strpos($outermostClassCode, 'level-top') > 0)
            {    
                $parent_url = $child->getUrl();
                $add_div = '<div class="dckap-jmenu" style="display:none;"><h5> <a class="main-cat" id="'.$child->getId().'" onClick="window.location.href=\'' . $child->getUrl() . '\'" >'.$child->getName().'</a></h5>';
                /*$add_div = '<div class="dckap-jmenu" >';*/
                $close_div = '</div>';
            }else{
                $add_div = '' ;
                $close_div = '';
            }

            if($this->_getRenderedMenuItemAttributes($child) !='')
            {
                if($childLevel == 1)  {

                    $spl_catid = explode('-',$child->getId());
                    $cat_tot_col[$spl_catid[2]] = array('name'=>$child->getName(),'url'=>$child->getUrl(),'parent'=>'Y');

                    $subcategories = $child->getChildren();
                    $lim = 0;
                    foreach($subcategories as $subcat){ 
                        ++$lim;
                        $spl_scatid = explode('-',$subcat->getId());
                        $cat_tot_col[$spl_scatid[2]] = array('name'=>$subcat->getName(),'url'=>$subcat->getUrl(),'parent'=>'N');
                        if($lim == 10){   
                            $lim = 0;
                            $cat_tot_col[$spl_catid[2].'-all'] = array('name'=>'See All >>','url'=>$child->getUrl(),'parent'=>'');
                            break;
                        }
                    }

                }
                else 
                {
                    $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
                    $html .= '<a id="'.$child->getId().'" href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml($child->getName()) . '</span></a>' .$add_div. $this->_addSubMenu($child,$childLevel,$childrenWrapClass,$limit) . $close_div.'</li>';
                }  

            } 
            else
            {
                $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . ' id="'.$child->getId().'"><span class="ll">' . $this->escapeHtml($child->getName()) . '</span></a>' .$add_div. $this->_addSubMenu($child,$childLevel,$childrenWrapClass,$limit) ;
                if($i == 9)
                {
                    $html .='<a class="seeAll" style="font-weight:bold" href="'.$parent_url.'" >See All >> </a>';
                    break;
                }
            }

            $itemPosition++;
            $counter++;
            $i++;
        }
        if($childLevel == 1){
            $i = 0; $tcnt = 0;
            if(count($cat_tot_col) > 0 && count($cat_tot_col) <= 7)
                $cols = 10;
            else if(count($cat_tot_col) >= 8  && count($cat_tot_col) <= 20)
                $cols = count($cat_tot_col)/2;
            else if(count($cat_tot_col) >= 21  && count($cat_tot_col) <= 60)
                $cols = count($cat_tot_col)/3;
            else
                $cols = count($cat_tot_col)/4;

            foreach($cat_tot_col as $id => $value){
                ++$i; ++$tcnt;
                if($value['parent'] == 'Y')
                    $class = "parent";
                else if ($value['parent'] == 'N')
                    $class = "child";
                else
                    $class = "last";
                $html .= '<li class="'.$class.'" id="menu-item-'.$id.'"><a style="cursor:pointer" onClick="window.location.href=\'' . $value['url'] . '\'" ><span>'.$this->escapeHtml($value['name']) . '</span></a></li>';

                if(ceil($cols) == $i){
                    $i=0;
                    if($tcnt == count($cat_tot_col))
                        $html .= '</ul>';
                    else
                        $html .= '</ul><ul class="jumbo_sub_menu">';
                }

            }

        }
        if (count($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }


    protected function _getRenderedMenuItemAttributes(\Magento\Framework\Data\Tree\Node $item)
    {
        $html = '';
        $attributes = $this->_getMenuItemAttributes($item);
        if($attributes != 'empty')
        {
            foreach ($attributes as $attributeName => $attributeValue) {
                $html .= ' ' . $attributeName . '="' . str_replace('"', '\"', $attributeValue) . '"';
            }   
        }

        return $html;
    }


    /**
    * Returns array of menu item's attributes
    *
    * @param \Magento\Framework\Data\Tree\Node $item
    * @return array
    */
    protected function _getMenuItemAttributes(\Magento\Framework\Data\Tree\Node $item)
    {
        $menuItemClasses = $this->_getMenuItemClasses($item);
        if(!empty($this->_getMenuItemClasses($item) ))
        {
            return ['class' => implode(' ', $menuItemClasses)];
        }else
        {
            return 'empty';
        }
    }

    /**
    * Returns array of menu item's classes
    *
    * @param \Magento\Framework\Data\Tree\Node $item
    * @return array
    */
    protected function _getMenuItemClasses(\Magento\Framework\Data\Tree\Node $item)
    {
        $classes = [];

        if($item->getLevel() <= 1)
        {
            $classes[] = 'level' . $item->getLevel();
            $classes[] = $item->getPositionClass();

            if ($item->getIsFirst()) {
                $classes[] = 'first';
            }

            if ($item->getIsActive()) {
                $classes[] = 'active';
            } elseif ($item->getHasActive()) {
                $classes[] = 'has-active';
            }

            if ($item->getIsLast()) {
                $classes[] = 'last';
            }

            if ($item->getClass()) {
                $classes[] = $item->getClass();
            }

            if ($item->hasChildren()) {
                $classes[] = 'parent';
            }
        }
        return $classes;
    }

    /* new one */

    public function getDckapHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {


        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_before',
            ['menu' => $this->_menu, 'block' => $this]
            );

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getDckapHtml($this->_menu, $childrenWrapClass, $limit);

        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_after',
            ['menu' => $this->_menu, 'transportObject' => $transportObject]
            );
        $html = $transportObject->getDckapHtml();

        return $html;
    }



    /**
    * Add sub menu HTML code for current menu item
    *
    * @param \Magento\Framework\Data\Tree\Node $child
    * @param string $childLevel
    * @param string $childrenWrapClass
    * @param int $limit
    * @return string HTML code
    */
    protected function _addDckapSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        if (!$child->hasChildren()) {
            return $html;
        }

        $colStops = null;
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }

        $html .= '<ul class="level' . $childLevel . ' dl-submenu">';
        $html .= $this->_getDckapHtml($child, $childrenWrapClass, $limit, $colStops);
        $html .= '</ul>';

        return $html;
    }

    protected function _getDckapHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        $colBrakes = []
        ) {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        foreach ($children as $child) {

            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            if (count($colBrakes) && $colBrakes[$counter]['colbrake']) {
                $html .= '</ul></li><li class="column"><ul>';
            }

            $sub_html = $this->_addDckapSubMenu($child, $childLevel,$childrenWrapClass,$limit) ;
            if($sub_html == '')
            {
                $action_url = $child->getUrl();
            }else
            {
                $action_url = 'javascript:void(0)';
            }
            $html .= '<li ' . $this->_getDckapRenderedMenuItemAttributes($child) . '>';
            $html .= '<a href="' . $action_url . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
                $child->getName()
                ) . '</span></a>' .$sub_html . '</li>';
            $itemPosition++;
            $counter++;
        }

        if (count($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }

    /**
    * Generates string with all attributes that should be present in menu item element
    *
    * @param \Magento\Framework\Data\Tree\Node $item
    * @return string
    */
    protected function _getDckapRenderedMenuItemAttributes(\Magento\Framework\Data\Tree\Node $item)
    {
        $html = '';
        $attributes = $this->_getDckapMenuItemAttributes($item);
        foreach ($attributes as $attributeName => $attributeValue) {
            $html .= ' ' . $attributeName . '="' . str_replace('"', '\"', $attributeValue) . '"';
        }
        return $html;
    }

    /**
    * Returns array of menu item's attributes
    *
    * @param \Magento\Framework\Data\Tree\Node $item
    * @return array
    */
    protected function _getDckapMenuItemAttributes(\Magento\Framework\Data\Tree\Node $item)
    {
        $menuItemClasses = $this->_getDckapMenuItemClasses($item);
        return ['class' => implode(' ', $menuItemClasses)];
    }

    /**
    * Returns array of menu item's classes
    *
    * @param \Magento\Framework\Data\Tree\Node $item
    * @return array
    */
    protected function _getDckapMenuItemClasses(\Magento\Framework\Data\Tree\Node $item)
    {
        $classes = [];

        $classes[] = 'level' . $item->getLevel();
        $classes[] = $item->getPositionClass();

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        } elseif ($item->getHasActive()) {
            $classes[] = 'has-active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren()) {
            $classes[] = 'parent';
        }

        return $classes;
    }

}