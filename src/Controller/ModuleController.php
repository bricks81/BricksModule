<?php

/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * The MIT License (MIT)
 * Copyright (c) 2015 bricks-cms.org
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Bricks\Module\Controller;

use Bricks\Config\ConfigAwareInterface;
use Bricks\Config\ConfigInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Bricks\Cms\Zend\ModuleManager\ModuleManagerAwareInterface;
use Bricks\Acl\Controller\AclTrait;

class IndexController extends AbstractActionController
implements ModuleManagerAwareInterface, ConfigAwareInterface {

    use AclTrait;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ModuleManagerInterface
     */
    protected $moduleManager;

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config){
        $this->config = $config;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig(){
        return $this->config;
    }

    /**
     * @param ModuleManagerInterface $moduleManager
     */
    public function setModuleManager(ModuleManagerInterface $moduleManager){
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return ModuleManagerInterface
     */
    public function getModuleManager(){
        return $this->moduleManager;
    }

    public function indexAction(){

        $this->Layout()->setTemplate('layout/backend');

        $path = $this->getConfig()->get('pathes.vendor');

        $dirlist = glob($path.'/bricks81/*');
        $allModules = array();
        foreach($dirlist AS $path){
            $string = basename($path);
            $parts = explode('-',$string);
            if($parts[0] == 'bricks'){
                $name = str_replace(' ','',ucwords(implode(' ',array_slice($parts,1))));
                if('_' == $name[0]){
                    continue;
                }
                $class = 'Bricks\\'.$name.'\\Module';
                if(class_exists($class)) {
                    if(defined($class.'::VERSION')) {
                        $allModules[] = array(
                            'name' => 'Bricks\\'.basename(dirname($class)),
                            'version' => $class::VERSION
                        );
                    }
                }
            }
        }

        $modules = array();
        foreach($this->getModuleManager()->getLoadedModules() AS $module){
            $class = get_class($module);
            if(substr($class,0,7) == 'Bricks\\'){
                $modules[] = 'Bricks\\'.basename(dirname($class));
            }
        }

        $view = new ViewModel();

        $view->setVariable('allModules',$allModules);
        $view->setVariable('modules',$modules);

        $view->setTemplate('bricks-cms/module/index');
        return $view;

    }

}