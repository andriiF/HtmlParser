<?php

class Parser {

    private $action;

    function __construct($action) {
        $this->action = $action;
    }

    public function getResult($url = "") {
        if (!preg_match('/^http|https:+/', $url)) {
            $url = 'http://' . $url;
        }
        switch ($this->action) {
            case "parse":
                $this->doParser($url);
                print "End of parsing!@ link to file -->" . realpath("result.csv");
                break;
            case "report":
                $this->doParser($url);
                break;
            case "help":
                $this->doHelp();
                break;
        }
    }

    private function doParser($url) {

        try {
            $html = file_get_contents($url);
        } catch (Exception $e) {
            echo $url . "\n" . $e;
        }
        $this->registerImage($url, $html);

        self::registerSubPage($url, $html);
    }

    private function doHelp() {

        echo "parse - запускает парсер, принимает обязательный параметр url (как с протоколом, так и без).\nreport - выводит в консоль результаты анализа для домена, принимает обязательный параметр domain\nhelp - выводит список команд";
    }

    private static function getSrc($tag) {
        $tag = preg_replace("/'/", '"', $tag);
        preg_match_all('/src="([^"]*)"/', $tag, $sourse);
        return $sourse[1][0];
    }

    private function registerImage($url, $html) {
        preg_match_all('/<img [^>]+>/i', $html, $result);
        $img_src = [];
        foreach ($result[0] as $tag) {
            $src = self::getSrc($tag);
            $parse_url = parse_url($url)["host"];
            if (!preg_match('/^http|https:+/', $src)) {
                $img_src[] = $url . "," . $parse_url . $src;
            } else {
                $img_src[] = $url . "," . $src;
            }
        }
        if ($this->action == "parse") {

            self::writeToFile($img_src);
        } else {
            self::writeToConsole($img_src);
        }
    }

    private static function registerSubPage($url, $html) {
        preg_match_all('/<a href = "([^"]*)"/', $html, $result);
        foreach ($result[1] as $suburl) {
            if ($suburl == "/" || $suburl == "" || $suburl == " " || $suburl == "#") {
                continue;
            } else {
                if (preg_match('/^http|https:+/', $suburl)) {
                    self::doParser($suburl);
                } else {
                    self::doParser($url . $suburl);
                }
            }
        }
    }

    private static function writeToFile($img_src) {
        $file = fopen("result.csv", "a");
        foreach ($img_src as $img) {
            fputcsv($file, explode(', ', $img));
        }
        fclose($file);
    }

    private static function writeToConsole($img_src) {
        foreach ($img_src as $img) {
            $temp = explode(",", $img);
            echo "Page url:" . $temp[0] . "\n Image: " . $temp[1] . "\n";
        }
    }

}
