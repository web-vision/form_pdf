<?php

namespace Brightside\FormPdf\Ajax;

use Brightside\FormPdf\Service\PdfService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PdfResponse
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Mpdf\MpdfException
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     */
    public function processRequest(ServerRequestInterface $request)
    {
        $response = GeneralUtility::makeInstance(Response::class);
        $param = $request->getQueryParams();
        $mpdf = null;
        if (isset($param['file']) && $param['file']) {
            $mpdf = $this->pdf($param['file']);
        }
        if ($mpdf) {
            $filename = $this->filename = $GLOBALS['TYPO3_REQUEST']->getParsedBody()['filename'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['filename'] ?? null;
            $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        } else {
            $response->getBody()->write('<h1>Error</h1><p>The file was deleted from the server after you opened it.<br />It can not be reloaded or saved again without submitting the form again.</p>');
            return $response->withStatus(404);
        }

        return $response;
    }

    /**
     * @param $uploadedTempFileName
     * @return \Mpdf\Mpdf|null
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     */
    private function pdf($uploadedTempFileName)
    {
        $uploadedTempFile = Environment::getVarPath() . '/transient/' . $uploadedTempFileName;
        $uploadedTempFile = GeneralUtility::fixWindowsFilePath($uploadedTempFile);
        if (
            GeneralUtility::validPathStr($uploadedTempFile)
            && @is_file($uploadedTempFile)
        ) {
            $mpdf = new \Mpdf\Mpdf(['tempDir' => Environment::getVarPath()]);
            $mpdf->SetDocTemplate($uploadedTempFile);
            $pagecount = $mpdf->SetSourceFile($uploadedTempFile);
            for ($i=1; $i<=$pagecount; $i++) {
                $import_page = $mpdf->importPage($i);
                $mpdf->useTemplate($import_page);
                if ($i < $pagecount) $mpdf->AddPage();
            }

            // Delete tmp file
            @unlink($uploadedTempFile);
            return $mpdf;
        }

        return null;
    }
}
