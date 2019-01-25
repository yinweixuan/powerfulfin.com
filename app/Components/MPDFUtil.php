<?php

namespace App\Components;

use App\Models\Fcs\FcsContract;

class MPDFUtil {
    /**
     * 输出结果文件
     */
    const DEST_OUTPUT = 'D';

    /**
     * 保存成文件
     */
    const DEST_FILE = "F";

    /**
     *  创建PDF
     */
    public static function gen($title, $content, $fileName, $outputDest = self::DEST_OUTPUT, $margin_top = 27) {
        $pdf = new \Mpdf\Mpdf([
            'mode' => 'zh-cn',
            'format' => 'A4',
        ]);
        $pdf->pdf_version = '1.5';
        $pdf->SetCreator('powerfulfin.com');
        $pdf->SetAuthor('powerfulfin.com');
        $pdf->SetTitle($title);
        $pdf->SetSubject('powerfulfin.com');
        $pdf->SetKeywords('powerfulfin');
        // 设置页脚
        $pdf->SetFooter('{PAGENO}');
        // 设置边距
        $pdf->SetMargins(15, 15, $margin_top);
        $pdf->SetAutoPageBreak(true, 20);
        // 设置字体
        $pdf->useFixedNormalLineHeight = false;
        $pdf->useFixedTextBaseline = false;
        $pdf->adjustFontDescLineheight = 1;

        $pdf->ignore_invalid_utf8 = true;
        $pdf->text_input_as_HTML = true;
        $pdf->useAdobeCJK = true;
        $pdf->autoScriptToLang = true;
        $pdf->autoLangToFont = true;
        $pdf->showWatermarkText = true;

        $pdf->AddPage();
        $pdf->WriteHTML($content);
        $pdf->Output($fileName, $outputDest);
    }
}