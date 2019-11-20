<?php


class CController {
    public $module;
    public $controller;
    public $action;
    public $layout = 'layouts/main';


    protected function fix_template_path($template) {
        return sprintf('%s/%s/views/%s/%s', Dispatcher::$config['root'], $this->module, $this->controller, $template);
    }

    /**
     * 渲染php文件，并返回渲染后的效果
     * @param string $template_path 文件路径
     * @param array $variables
     * @return string 渲染后的字符
     */
    protected function renderFile($template_path, $variables) {
        extract($variables);
        ob_start();
        ob_implicit_flush(false);
        require $template_path . '.php';
        return ob_get_clean();
    }

    /**
     * 不带布局文件
     * @param string $template 视图文件名字，不带后缀
     * @param $variables
     */
    protected function renderPartial($template, $variables = array()) {
        extract($variables);
        $template_path = $this->fix_template_path($template);
        require $template_path . '.php';
    }

    /**
     * 带布局文件
     * @param string $template 视图文件名字，不带后缀
     * @param $variables
     */
    protected function render($template, $variables = array()) {
        extract($variables);
        $content = $this->renderFile($this->fix_template_path($template), $variables);
        if ($this->layout) {
            require sprintf('%s/%s/views/%s', Dispatcher::$config['root'], $this->module, $this->layout) . '.php';
        } else {
            echo '布局文件不存在';
        }
    }

    protected function renderWidget($template, $variables = array()) {
        extract($variables);
        require sprintf('%s/%s', Dispatcher::$config['widgets'], $template) . '.php';
    }

    protected function renderCustom($template, $variables = array()) {
        extract($variables);
        require sprintf('%s/%s', Dispatcher::$config['root'], trim($template, '/')) . '.php';
    }

}