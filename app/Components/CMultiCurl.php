<?php

namespace App\Components;
/**
 * 并行拉取多个url
 */
class CMultiCurl
{

    private $mch;
    private $timeout;
    private $urls;
    private $chs;
    private $errchs;
    private $content;

    public function __construct($urls, $timeout = 300)
    {

        $this->mch = curl_multi_init();

        $this->timeout = $timeout;

        $this->urls = $urls;

        $this->chs = array();

        $this->errchs = array();

        $this->content = array();
    }

    public function getError()
    {
        return $this->errchs;
    }

    public function ctlFlow()
    {

        $this->createhandle();

        $this->execurl();

        $this->chs = array();

        $this->errchs = array();
        return $this->content;
    }

    private function createhandle()
    {

        if (!empty($this->urls)) {

            foreach ($this->urls as $k => $url) {

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $url);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

                curl_setopt($ch, CURLOPT_PROXY, StaticConstant::HTTP_PROXY);

                curl_multi_add_handle($this->mch, $ch);

                $this->chs[$k] = $ch;
            }
        }
    }

    private function execurl()
    {

        do {

            $mrc = curl_multi_exec($this->mch, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);


        while ($active && $mrc == CURLM_OK) {

            if (curl_multi_select($this->mch) != -1) {

                usleep(300);

                do {

                    $mrc = curl_multi_exec($this->mch, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }


        foreach ($this->chs as $k => $c) {

            if (curl_error($c) == '') {

                $temp = curl_multi_getcontent($c);

                $count = 0;

                while (!$temp) {

                    if ($count > 3)
                        break;

                    usleep(100);

                    $temp = curl_multi_getcontent($c);

                    $count++;
                }

                $this->content[$k] = $temp;

                unset($this->chs[$k]);

                curl_multi_remove_handle($this->mch, $c);

                curl_close($c);
            } else {

                $this->errchs[$k] = $this->urls[$k];
            }
        }
    }

}
