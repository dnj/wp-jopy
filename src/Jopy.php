<?php

class Jopy
{
    /**
     * @var self|null
     */
    protected static $instance;

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return void
     */
    public function registerHooks()
    {
        register_activation_hook(JOPY_PLUGIN_FILE, [$this, 'enable']);
        add_action('activated_plugin', [$this, 'onUpdatePlugins'], PHP_INT_MAX);
        add_action('deactivated_plugin', [$this, 'onUpdatePlugins'], PHP_INT_MAX);
        add_action('upgrader_process_complete', [$this, 'onUpgrade'], PHP_INT_MAX);
        register_deactivation_hook(JOPY_PLUGIN_FILE, [$this, 'disable']);
    }

    /**
     * @return string[]
     */
    public function filesToChange()
    {
        return [
            ABSPATH.WPINC.'/update.php',
            ABSPATH.'wp-admin/includes/dashboard.php',
            ABSPATH.'wp-admin/includes/plugin-install.php',
            ABSPATH.'wp-admin/includes/update.php',
            ABSPATH.'wp-admin/includes/theme.php',
            ABSPATH.'wp-admin/includes/misc.php',
            ABSPATH.'wp-admin/includes/translation-install.php',
        ];
    }

    public function getUrlForAPI(): string
    {
        return 'jopy.ir/wordpress/api';
    }

    public function enable()
    {
        $urlForApi = $this->getUrlForAPI();
        foreach ($this->filesToChange() as $path) {
            $this->replaceInFile($path, '/api.wordpress.org/', "/{$urlForApi}/");
        }
        $this->deleteTransients();
    }

    public function disable()
    {
        $urlForApi = $this->getUrlForAPI();
        foreach ($this->filesToChange() as $path) {
            $this->replaceInFile($path, "/{$urlForApi}/", '/api.wordpress.org/');
        }
        $this->deleteTransients();
    }

    /**
     * @param string $plugin path to the plugin file relative to the plugins directory
     *
     * @return void
     */
    public function onUpdatePlugins($plugin)
    {
        if ('wp-jopy/' == substr($plugin, 0, strlen('wp-jopy/'))) {
            return;
        }

        $this->enable();
    }

    /**
     * @return void
     */
    public function onUpgrade()
    {
        $this->enable();
    }

    /**
     * @param string $path
     * @param string $from
     * @param string $to
     *
     * @return void
     */
    public function replaceInFile($path, $from, $to)
    {
        if (!is_file($path) or !is_readable($path)) {
            return;
        }
        if (!is_writeable($path)) {
            throw new Exception("file {$path} is not writable");
        }
        $content = file_get_contents($path);
        $content = str_replace($from, $to, $content);
        file_put_contents($path, $content);
    }

    /**
     * @return void
     */
    public function deleteTransients()
    {
        delete_site_transient('update_core');
        delete_site_transient('available_translations');
        delete_site_transient('update_plugins');
    }
}
