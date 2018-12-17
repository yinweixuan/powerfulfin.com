<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:12 PM
 */

namespace App\Models\Jcfc;

require_once PATH_VENDOR . '/autoload.php';

use App\Components\PFException;

class JcfcSFTP
{
//正式
    const JCFC_HOST = 'file.jcfc.cn';
    const JCFC_PORT = 3022;
    const JCFC_USER = 'kezhan';
    const JCFC_PASSWORD = 'p%8gBu*y2y';
    //测试
    const JCFC_TEST_HOST = 'test.jcfc.cn';
    const JCFC_TEST_PORT = 22;
    const JCFC_TEST_USER = 'kezhan';
    const JCFC_TEST_PASSWORD = 'kezhan@123';

    public $connection;
    public $sftp;

    public function __construct()
    {

        if (config('app.env') != 'production') {
            //测试环境
            $this->sftp = new \phpseclib\Net\SFTP(self::JCFC_TEST_HOST, self::JCFC_TEST_PORT);
            for ($i = 0; $i < 3; $i++) {
                if ($this->sftp->login(self::JCFC_TEST_USER, self::JCFC_TEST_PASSWORD)) {
                    break;
                } else {
                    if ($i == 2) {
                        throw new PFException("SFTP链接失败");
                    }
                }
            }
        } else {
            //生产环境
            $this->sftp = new \phpseclib\Net\SFTP(self::JCFC_HOST, self::JCFC_PORT);
            for ($i = 0; $i < 3; $i++) {
                if ($this->sftp->login(self::JCFC_USER, self::JCFC_PASSWORD)) {
                    break;
                } else {
                    if ($i == 2) {
                        throw new PFException("SFTP链接失败");
                    }
                }
            }
        }
    }

    /**
     * 文件上传
     * @param string $remote_dirkey 上传目录的key，例：
     * <br>apply 贷款申请主键命名/影像；(贷款申请阶段上传影像)
     * <br>contract 贷款申请主键命名（合同下载）
     * <br>image 合同签订申请主键命名/影像；（合同签订阶段上传影像）
     * <br>filldoc 补传的影像资料
     * <br>loan 存放放款文件
     * <br>payloan 还款计划文件
     * <br>repaydoc 存放当日的扣款文件
     * <br>overdue 上传逾期文件
     * <br>takepayment 代扣代付文件（批扣文件）
     * <br>sultchase 债券回购结果文件（全额代偿文件）
     * <br>prepayment 渠道方上传的还款文件
     * @param string $zipfile 生成的zip的文件名，包含本地路径
     * @param array $file_list 需要打包成zip的本地文件列表
     * @return string 远程文件名，包含远程路径
     */
    public function upload($remote_dirkey, $zipfile, $file_list, $applseq)
    {
        $this->zip($zipfile, $file_list);
        $remote_dir = $this->getRemoteDir($remote_dirkey, $applseq);
        $remote_file = $remote_dir . DIRECTORY_SEPARATOR . basename($zipfile);
        $this->baseUpload($zipfile, $remote_file);
        return $remote_file;
    }

    /**
     * 文件下载
     * @param string $remote_file 远程文件
     * @param string $local_file 本地文件
     */
    public function download($remote_file, $local_file)
    {
        $local_dir = dirname($local_file);
        if (!is_dir($local_dir)) {
            mkdir($local_dir, 0755, true);
        }

        if (!$this->sftp->file_exists($remote_file)) {
            throw new PFException("为查询到远程文件");
        }
        $this->sftp->get($remote_file, $local_file);
    }

    public function baseUpload($local_file, $remote_file)
    {
        try {
            return $this->sftp->put($remote_file, $local_file, \phpseclib\Net\SFTP::SOURCE_LOCAL_FILE);
        } catch (PFException $PFException) {
            throw new PFException($PFException->getMessage());
        }
    }

    public function zip($zipfile, $file_list)
    {
        $zipdir = dirname($zipfile);
        if (!is_dir($zipdir)) {
            mkdir($zipdir, 0755, true);
        }
        $zip = new ZipArchive();
        $r = $zip->open($zipfile, ZIPARCHIVE::CREATE);
        if ($r !== TRUE) {
            throw new PFException("Could not open a new zip archive: $r.");
        }
        foreach ($file_list as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, basename($file));
            }
        }
        $zip->close();
        if (!is_file($zipfile)) {
            throw new PFException("生成zip文件失败: $zipfile.");
        }
    }

    public function getRemoteDir($key, $applseq = '')
    {
        $dir_arr = array(
            //SFTP上传下载影像资料的路径
            'apply' => '/upload/apply', //贷款申请阶段上传影像
            'contract' => '/upload/contract', //合同下载
            'sign' => '/upload/sign',   //合同签订阶段上传影像
            'supply' => '/upload/supply',   //补传的影像资料
            //SFTP存放报表文件路径
            'loan' => '/upload/loan', //存放放款文件
            'repayplan' => '/upload/repayplan', //存放还款计划文件
            'repay' => '/upload/repay', //存放还款文件
            'overdue' => '/upload/overdue', //存放逾期文件
            'recourse' => '/upload/recourse',   //存放代偿文件
            'agencyfund' => '/upload/agencyfund',   //合作方上传的代扣款文件
            'collection' => '/upload/collection',    //合作方上传的委托扣款文件，合作方需提供代扣明细，晋消做扣款清分时用
            'subrogation' => '/upload/subrogation',  //合作方上传的已代偿文件
        );
        $dir = $dir_arr[$key];
        if ($dir) {
            if ($applseq) {
                $dir .= DIRECTORY_SEPARATOR . $applseq;
            }
            $this->sftp->mkdir($dir, 0755, true);
        } else {
            throw new PFException("Could not find the dir: $key.");
        }
        return $dir;
    }
}
