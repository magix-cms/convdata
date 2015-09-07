<?php
/*
 # -- BEGIN LICENSE BLOCK ----------------------------------
 #
 # This file is part of MAGIX CMS.
 # MAGIX CMS, The content management system optimized for users
 # Copyright (C) 2008 - 2013 magix-cms.com <support@magix-cms.com>
 #
 # OFFICIAL TEAM :
 #
 #   * Gerits Aurelien (Author - Developer) <aurelien@magix-cms.com> <contact@aurelien-gerits.be>
 #
 # Redistributions of files must retain the above copyright notice.
 # This program is free software: you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation, either version 3 of the License, or
 # (at your option) any later version.
 #
 # This program is distributed in the hope that it will be useful,
 # but WITHOUT ANY WARRANTY; without even the implied warranty of
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 # GNU General Public License for more details.

 # You should have received a copy of the GNU General Public License
 # along with this program.  If not, see <http://www.gnu.org/licenses/>.
 #
 # -- END LICENSE BLOCK -----------------------------------

 # DISCLAIMER

 # Do not edit or add to this file if you wish to upgrade MAGIX CMS to newer
 # versions in the future. If you wish to customize MAGIX CMS for your
 # needs please refer to http://www.magix-cms.com for more information.
 */
/**
 * MAGIX CMS
 * @category   clear 
 * @package    plugins
 * @copyright  MAGIX CMS Copyright (c) 2008 - 2014 Gerits Aurelien,
 * http://www.magix-cms.com,  http://www.magix-cjquery.com
 * @license    Dual licensed under the MIT or GPL Version 3 licenses.
 * @version    1.0
 * @author Gérits Aurélien <aurelien@magix-cms.com> <aurelien@magix-dev.be>
 *
 */
class plugins_convdata_admin extends DBConvData{
    protected $template;
	/**
	 * @access public
	 * @var POST clear
	 */
	public $action,$tab,$clear,$type;
	/**
	 * @access public
	 * Constructor
	 */
	function __construct(){
        $this->template = new backend_controller_plugins;
        if(magixcjquery_filter_request::isGet('action')){
            $this->action = magixcjquery_form_helpersforms::inputClean($_GET['action']);
        }
        if(magixcjquery_filter_request::isGet('tab')){
            $this->tab = magixcjquery_form_helpersforms::inputClean($_GET['tab']);
        }
		if(magixcjquery_filter_request::isPost('module')){
			$this->module = magixcjquery_form_helpersforms::inputClean($_POST['module']);
		}
        if(magixcjquery_filter_request::isPost('type')){
            $this->type = magixcjquery_form_helpersforms::inputClean($_POST['type']);
        }

	}
    /**
     * Change les clés du tableau
     * @param $arraySource
     * @param $keys
     * @return array
     */
    public function arrayChangeKeys($arraySource, $keys)
    {
        $newArray = array();
        foreach($arraySource as $key => $value)
        {
            $k = (array_key_exists($key, $keys)) ? $keys[$key] : $key;
            $v = ((is_array($value))) ? $this->arrayChangeKeys($value, $keys) : $value;
            $newArray[$k] = $v;
        }
        return $newArray;
    }
    /**
     * Retourne le message de notification
     * @param $type
     */
    private function notify($type){
        $this->template->assign('message',$type);
        $this->template->display('message.tpl');
    }

    /**
     * @param array $data
     * @param $module
     * @param $type
     * @param array $keys
     */
    public function setItemData(array $data,$module,$type,array $keys){
        if(count($keys) > 3){
            // define new array
            $newKey = array(
                $keys[0]  => 'id',
                $keys[1]  => 'title',
                $keys[2]  => 'content',
                $keys[3]  => 'seo_title',
                $keys[4]  => 'seo_desc'
            );
        }else{
            // define new array
            $newKey = array(
                $keys[0]  => 'id',
                $keys[1]  => 'title',
                $keys[2]  => 'content'
            );
        }

        if(method_exists('backend_controller_plugins','arrayChangeKeys')){
            $newData = $this->template->arrayChangeKeys($data, $newKey);
        }else{
            $newData = $this->arrayChangeKeys($data, $newKey);
        }

        //loop and update data
        foreach($newData as $key){
            if(count($keys) > 3) {
                $this->update(
                    $module,
                    $type,
                    array(
                        'id' => $key['id'],
                        'title' => $key['title'],
                        'content' => $key['content'],
                        'seo_title' => $key['seo_title'],
                        'seo_desc' => $key['seo_desc']
                    )
                );
            }else{
                $this->update(
                    $module,
                    $type,
                    array(
                        'id' => $key['id'],
                        'title' => $key['title'],
                        'content' => $key['content']
                    )
                );
            }
        }
    }

    /**
     * @param $module
     * @param $type
     */
    public function getItemData($module,$type){
        $data = parent::select($module,$type);
        if(is_array($data)){
            switch($module){
                case 'catalog':
                    if($type === 'product'){
                        $keys = array('idcatalog','titlecatalog','desccatalog');
                    }elseif($type === 'category'){
                        $keys = array('idclc','clibelle','c_content');
                    }elseif($type === 'subcategory'){
                        $keys = array('idcls','slibelle','s_content');
                    }else{
                        $keys = array('idcatalog','titlecatalog','desccatalog');
                    }
                    break;
                case 'news':
                    $keys = array('idnews','n_title','n_content');
                    break;
                case 'pages':
                    $keys = array('idpage','title_page','content_page','seo_title_page','seo_desc_page');
                    break;
                case 'rewrite':
                    $keys = array('idrewrite','attribute','strrewrite');
                    break;
            }
            //count($keys);

            $this->setItemData($data,$module,$type,$keys);
            $this->notify('update');
        }
    }
	/**
	 * @access public
	 * Execute le plugin
	 */
	public function run(){
        $create = new backend_controller_plugins();
        if(isset($this->tab)){
            $create->display('about.tpl');
        }else{
            if(isset($this->module)){
                if(!empty($this->module)){
                    $this->getItemData($this->module,$this->type);
                }
            }else{
                // Retourne la page index.tpl
                $create->display('index.tpl');
            }

        }
    }

    /**
     * Set Configuration pour le menu
     * @return array
     */
    public function setConfig(){
        return array(
            'url'   =>  array(
                'lang'  =>  'none',
                'action'=>  '',
                'name'  =>  'encodage'
            ),
            'icon'=> array(
                'type'=>'font',
                'name'=>'fa fa-file-text-o'
            )
        );
    }
}
class DBConvData{
    /**
     * @return array
     */
    protected function selectModule(){
        $query = 'SELECT * FROM mc_plugins_convdata';
        return magixglobal_model_db::layerDB()->select($query);
    }
    /**
     * @param string $module
     * @param null $type
     * @return array
     */
    protected function select($module = 'catalog',$type = null){
        switch($module){
            case 'catalog':
                if($type === 'product'){
                    $query = 'SELECT idcatalog, titlecatalog, desccatalog
		            FROM mc_catalog';
                }elseif($type === 'category'){
                    $query = 'SELECT idclc,clibelle,c_content
                    FROM mc_catalog_c';
                }elseif($type === 'subcategory'){
                    $query = 'SELECT idcls,slibelle,s_content
                    FROM mc_catalog_s';
                }else{
                    $query = 'SELECT idcatalog, titlecatalog, desccatalog
		            FROM mc_catalog';
                }
                break;
            case 'news':
                $query = 'SELECT idnews, n_title, n_content
		            FROM mc_news';
                break;
            case 'pages':
                $query = 'SELECT idpage, title_page, content_page, seo_title_page ,seo_desc_page
		            FROM mc_cms_pages';
                break;
            case 'rewrite':
                $query = 'SELECT idrewrite, attribute, strrewrite
		            FROM mc_metas_rewrite';
                break;
        }
        if($query){
            return magixglobal_model_db::layerDB()->select($query);
        }
    }

    /**
     * @param string $module
     * @param null $type
     * @param array $keys
     */
    protected function update($module = 'catalog',$type = null,array $keys){
        switch($module){
            case 'catalog':
                if($type === 'product'){
                    $query='UPDATE mc_catalog SET
                      titlecatalog=:title,desccatalog=:content
                      WHERE idcatalog=:id';
                }elseif($type === 'category'){
                    $query='UPDATE mc_catalog_c SET
                      clibelle=:title,c_content=:content
                      WHERE idclc=:id';
                }elseif($type === 'subcategory'){
                    $query='UPDATE mc_catalog_s SET
                      slibelle=:title,s_content=:content
                      WHERE idcls=:id';
                }else{
                    $query='UPDATE mc_catalog SET
                      titlecatalog=:title,desccatalog=:content
                      WHERE idcatalog=:id';
                }
                break;
            case 'news':
                $query = 'UPDATE mc_news SET
                      n_title=:title,n_content=:content
                      WHERE idnews=:id';
                break;
            case 'pages':
                $query = 'UPDATE mc_cms_pages SET
                      title_page=:title,content_page=:content,seo_title_page=:seo_title,seo_desc_page=:seo_desc
                      WHERE idpage=:id';
                break;
            case 'rewrite':
                $query = 'UPDATE mc_metas_rewrite SET
                      attribute=:title,strrewrite=:content
                      WHERE idrewrite=:id';
                break;
        }
        if($query){
            if(count($keys) > 3) {
                magixglobal_model_db::layerDB()->update($query,
                    array(
                        ':id'       => $keys['id'],
                        ':title'    => utf8_decode($keys['title']),
                        ':content'  => utf8_decode($keys['content']),
                        ':seo_title'=> utf8_encode($keys['seo_title']),
                        ':seo_desc' => utf8_decode($keys['seo_desc'])
                    )
                );
            }else{
                magixglobal_model_db::layerDB()->update($query,
                    array(
                        ':id'       => $keys['id'],
                        ':title'    => utf8_decode($keys['title']),
                        ':content'  => utf8_decode($keys['content'])
                    )
                );
            }
        }
    }
}