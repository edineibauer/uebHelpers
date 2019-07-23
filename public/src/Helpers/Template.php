<?php

namespace Helpers;

class Template
{
    private $library;
    private $folder;
    private $smart;

    public function __construct($library = null)
    {
        if ($library)
            $this->setLibrary($library);
    }

    /**
     * @param mixed $library
     */
    public function setLibrary($library)
    {
        $this->library = $library;
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function getShow(string $template, array $data = null): string
    {
        return $this->prepareShow($template, $data);
    }

    /**
     * @param string $template
     * @param array $data
     */
    public function show(string $template, array $data = null)
    {
        echo $this->prepareShow($template, $data);
    }

    private function prepareShow(string $template, array $data = null): string
    {
        $this->getFolderLocation($template);

        if (!empty($this->folder)) {
            $this->start($data);

            $retorno = $this->smart->fetch($template . ".tpl");

            $this->smart->clearAllAssign();

            return $retorno;
        }

        return "";
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        foreach ($data as $name => $value) {
            $this->smart->assign($name, $value);
        }
    }

    public function clearData()
    {
        $this->smart->clearAllAssign();
    }

    /**
     * @param string $template
     */
    private function getFolderLocation(string $template)
    {
        if (empty($this->folder)) {
            if (!empty($this->library)) {
                $base = (defined('DOMINIO') && $this->library === DOMINIO ? "public/tpl" : VENDOR . "{$this->library}/public/tpl");
                $this->checkTemplateExist($base, $template);

                if (empty($this->folder) && !empty($_SESSION['userlogin']['setor'])) {
                    $base .= "/" . $_SESSION['userlogin']['setor'];
                    $this->checkTemplateExist($base, $template);
                }
            } else {

                // Busca template

                //public
                $base = "public/tpl";
                $this->checkTemplateExist($base, $template);

                if (empty($this->folder)) {

                    //public login
                    if(!empty($_SESSION['userlogin']['setor'])) {
                        $base = "public/tpl/" . $_SESSION['userlogin']['setor'];
                        $this->checkTemplateExist($base, $template);
                    }
                    if (empty($this->folder)) {
                        foreach (Helper::listFolder(PATH_HOME . VENDOR) as $lib) {
                            if (empty($this->folder)) {

                                //lib
                                $base = VENDOR . "{$lib}/public/tpl";
                                $this->checkTemplateExist($base, $template);
                                if (empty($this->folder) && !empty($_SESSION['userlogin']['setor'])) {

                                    //lib and login
                                    $base = VENDOR . "{$lib}/public/tpl/" . $_SESSION['userlogin']['setor'];
                                    $this->checkTemplateExist($base, $template);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $dir
     * @param string $template
     */
    private function checkTemplateExist(string $dir, string $template)
    {
        if (file_exists(PATH_HOME . $dir . "/{$template}.tpl"))
            $this->folder = $dir;
        elseif (!empty($_SESSION['userlogin']['setor']) && file_exists(PATH_HOME . $dir . "/" . $_SESSION['userlogin']['setor'] . "/{$template}.tpl")) {
            $this->folder = $dir . "/" . $_SESSION['userlogin']['setor'];
        }
    }

    /**
     * @param array|null $data
     */
    private function start(array $data = null)
    {
        $this->smart = new \Smarty();
        $this->preData();

        if ($data)
            $this->setData($data);

        $this->smart->setTemplateDir($this->folder);
    }

    private function preData()
    {
        $this->smart->assign("datetime", date("d/m/Y H:i:s"));
        $this->smart->assign("date", date("d/m/Y"));
        $this->smart->assign("year", date("Y"));
        $this->smart->assign("hora", date("H:i"));
        $this->smart->assign("user", json_encode((!empty($_SESSION['userlogin']) ? $_SESSION['userlogin'] : []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->smart->assign("pushpublic", (defined('PUSH_PUBLIC_KEY') ? PUSH_PUBLIC_KEY : ""));
        if (defined('HOME')) $this->smart->assign("home", HOME);
        if (defined('PATH_HOME')) $this->smart->assign("path_home", PATH_HOME);
        if (defined('LOGO')) $this->smart->assign("logo", LOGO);
        if (defined('FAVICON')) $this->smart->assign("favicon", FAVICON);
        if (defined('SITENAME')) $this->smart->assign("sitename", SITENAME);
        if (defined('SITESUB')) $this->smart->assign("sitesub", SITESUB);
        if (defined('SITEDESC')) $this->smart->assign("sitedesc", SITEDESC);
        if (defined('HOMEPAGE')) $this->smart->assign("homepage", HOMEPAGE);
        if (defined('VERSION')) $this->smart->assign("version", VERSION);
        if (defined('DOMINIO')) $this->smart->assign("dominio", DOMINIO);
        if (defined('VENDOR')) $this->smart->assign("vendor", VENDOR);
        if (defined('AUTOSYNC')) $this->smart->assign("autosync", AUTOSYNC);
        if (defined('LIMITOFFLINE')) $this->smart->assign("limitoffline", LIMITOFFLINE);

        if (file_exists(PATH_HOME . "public/assets/theme.min.css")) {
            $f = file_get_contents(PATH_HOME . "public/assets/theme.min.css");
            if(preg_match('/\.theme\{/i', $f)) {
                $theme = explode(".theme{", $f)[1];
                $themeb = explode("!important", explode("background-color:", $theme)[1])[0];
                $themec = explode("!important", explode("color:", $theme)[1])[0];
                if (!empty($themeb))
                    $this->smart->assign("theme", $themeb);
                if (!empty($themec))
                    $this->smart->assign("themeColor", $themec);
            } else {
                $this->smart->assign("theme", "#2196f3");
                $this->smart->assign("themeColor", "#ffffff");
            }
        } else {
            $this->smart->assign("theme", "#2196f3");
            $this->smart->assign("themeColor", "#ffffff");
        }
    }
}